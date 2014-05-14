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
        $state    = new State($address->id_state);

        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

        $streetNumber = preg_replace('/\D/', '', $address->address1);
        $streetNumber = ($streetNumber > 0) ? $streetNumber : '1';

        $params = array(
            'mode'      => 'full'
          , 'operation' => 'request'
          , 'payment'   => array(
              'payment_type_code' => Tools::getValue('ebanx_payment_type_code')
            , 'amount_total'      => $cart->getOrderTotal(true)
            , 'currency_code'     => $currency->iso_code
            , 'merchant_payment_code' => $cart->id
            , 'name'          => $customer->firstname . ' ' . $customer->lastname
            , 'birth_date'    => Tools::getValue('ebanx_birth_date')
            , 'document'      => Tools::getValue('ebanx_document')
            , 'email'         => $customer->email
            , 'address'       => $address->address1 . ' ' . $address->address2
            , 'street_number' => $streetNumber
            , 'state'         => $state->iso_code
            , 'zipcode'       => $address->postcode
            , 'city'          => $address->city
            , 'country'       => 'br'
            , 'phone_number'  => $address->phone
          )
        );

        $response = \Ebanx\Ebanx::doRequest($params);

        if ($response->status == 'SUCCESS')
        {
            // Create a new order via validateOrder()
            $ebanx = new Ebanx();
            $ebanx->validateOrder($cart->id, Configuration::get('EBANX_STATUS_OPEN'), $total, $ebanx->displayName);

            // If the request was successfull, create a new order
            $order = new Order($ebanx->currentOrder);

            $method = Tools::getValue('ebanx_payment_method');
            $hash   = $response->payment->hash;

            if ($method == 'boleto')
            {
                $ebanx->saveOrderData($order->id, $hash, $method, $response->payment->boleto_url);
                Tools::redirect('index.php?fc=module&module=ebanx&controller=success&hash=' . $hash);
            }
            else if ($method == 'tef')
            {
              $ebanx->saveOrderData($order->id, $hash, $method);
              Tools::redirect($response->redirect_url);
            }
            else
            {
              $ebanx->saveOrderData($order->id, $hash, $method);
              Tools::redirect('index.php?fc=module&module=ebanx&controller=success&hash=' . $hash);
            }
        }
        else
        {
            // Go back to the other screen
            Tools::redirect($_SERVER['HTTP_REFERER'] . '&ebanx_error=' . urlencode($response->status_message));
        }
    }
}