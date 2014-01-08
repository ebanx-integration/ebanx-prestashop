<?php

/**
 * Copyright (c) 2014, EBANX Tecnologia da Informação Ltda.
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

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

/**
 * The checkout controller. It creates a new order and redirects the user
 * to EBANX.
 */
class EbanxCheckoutModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $cart     = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $address  = new Address($cart->id_address_invoice);

        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

        // Create a new order via validateOrder()
        $ebanx = new Ebanx();
        $ebanx->validateOrder($cart->id, Configuration::get('EBANX_STATUS_OPEN'), $total, $ebanx->displayName);

        $order = new Order($ebanx->currentOrder);

        $params = array(
            'payment_type_code' => '_all'
          , 'amount'            => $cart->getOrderTotal(true)
          , 'currency_code'     => $currency->iso_code
          , 'merchant_payment_code' => $cart->id
          , 'name'         => $customer->firstname . ' ' . $customer->lastname
          , 'email'        => $customer->email
          , 'address'      => $address->address1 . ' ' . $address->address2
          , 'zipcode'      => $address->postcode
          , 'city'         => $address->city
          , 'phone_number' => $address->phone
        );

        // Adds installments to order and updates order total if they are enabled.
        $installments = Tools::getValue('ebanx_installments');
        if (intval($installments) > 1)
        {
            $params['instalments']       = $installments;
            $params['payment_type_code'] = Tools::getValue('ebanx_installments_card');

            $interestRate = floatval(Configuration::get('EBANX_INTEREST_RATE'));
            if ($interestRate > 0)
            {
              $params['amount'] = ($total * (100 + $interestRate)) / 100.0;
            }
        }

        $response = \Ebanx\Ebanx::doRequest($params);

        if ($response->status == 'SUCCESS')
        {
            $ebanx->saveHash($order->id, $response->payment->hash);
            Tools::redirect($response->redirect_url);
        }
        else
        {
            var_dump($response);
            die('Erro!');
        }
    }
}