<?php

/**
 * Copyright (c) 2013, EBANX Tecnologia da Informação Ltda.
 *  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * Neither the name of EBANX nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * The payment controller. It builds the payment form.
 */
class EbanxExpressPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
      $this->ssl = (intval(Configuration::get('PS_SSL_ENABLED')) == 1) && (intval(Configuration::get('EBANX_EXPRESS_TESTING')) == 0);
      parent::__construct();
    }

    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();

        global $smarty;



        // Calculate the total and the total with interest
        $total    = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        $maxInstallments = intval(Configuration::get('EBANX_EXPRESS_INSTALLMENTS_NUM'));


        // Convert the total to BRL (approximation)
        switch (strtoupper(($this->context->currency->iso_code)))
        {
          case 'USD':
            $totalReal = $total * 2.5;
            break;
          case 'EUR':
            $totalReal = $total * 3.4;
            break;
          case 'BRL':
          default:
            $totalReal = $total;
            break;
        }

        // Enforce minimum installment value of R$25
        if (($totalReal / 35) < $maxInstallments)
        {
          $maxInstallments = floor($totalReal / 30);
        }


        $currency = new Currency($this->context->cart->id_currency);

        $smarty->assign(array(
            'action_url'          => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=ebanxexpress&controller=direct'
          , 'total'               => $total
          , 'image'               => __PS_BASE_URI__ . 'modules/ebanxexpress/assets/img/ebanx.png'
          , 'enable_installments' => (intval(Configuration::get('EBANX_EXPRESS_INSTALLMENTS_ACT')) == 1)
          , 'max_installments'    => $maxInstallments
          , 'currency_code'       => $this->context->currency->iso_code
          , 'request_error'       => Tools::getValue('ebanx_error')
          , 'installments_total'  => $this->getInstallmentsTotals()
          , 'has_interest'        => intval(Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT')) > 0
        ));



        // One template for each payment method
        $template = 'form_' . Tools::getValue('method') . '.tpl';
        $this->setTemplate($template);
    }

    public function getInstallmentsTotals()
    {
        $orderTotal      = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $interestRate    = floatval(Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT'));
        $maxInstallments = intval(Configuration::get('EBANX_EXPRESS_INSTALLMENTS_NUM'));
        $interestMode    = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_MOD');

        $totals = array();
        $totals[1] = $orderTotal;
        for ($i = 2; $i <= $maxInstallments; $i++)
        {
          $totals[$i] = EbanxExpress::calculateTotalWithInterest($interestMode, $interestRate, $orderTotal, $i);
        }

        return $totals;
    }
}