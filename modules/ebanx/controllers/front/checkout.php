<?php

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

class EbanxCheckoutModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $cart     = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $address  = new Address($cart->id_address_invoice);

        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

        $ebanx = new Ebanx();
        $ebanx->validateOrder($cart->id, 1, $total, $ebanx->displayName);

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

        // Adds installments
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