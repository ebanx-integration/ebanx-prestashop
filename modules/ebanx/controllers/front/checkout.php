<?php

class EbanxCheckoutModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $testMode = Configuration::get('EBANX_TESTING');
        $integrationKey = Configuration::get('EBANX_INTEGRATION_KEY');

        $cart = $this->context->cart;

        $action = 'https://www.ebanx.com/pay/ws/';
        if ($testMode == true)
        {
            $action = 'https://www.ebanx.com/test/ws/';
        }

        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $address  = new Address($cart->id_address_invoice);

        $params = 'integration_key=' . $integrationKey;
        $params .= '&payment_type_code=_all';

        $params .= '&amount=' . $cart->getOrderTotal(true);
        $params .= '&currency_code=' . $currency->iso_code;

        $params .= '&merchant_payment_code=' . $cart->id;

        $params .= '&name=' . $customer->firstname . ' ' . $customer->lastname;
        $params .= '&email=' . $customer->email;
        $params .= '&address=' . $address->address1 . ' ' . $address->address2;
        $params .= '&cpf=';
        $params .= '&birth_date=';
        $params .= '&zipcode=' . $address->postcode;
        $params .= '&city=' . $address->city;
        $params .= '&street_number=';
        $params .= '&phone_number=' . $address->phone;

        $ch = curl_init($action . 'request');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // RETURN THE CONTENTS OF THE CALL
        $json_response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($json_response);
        if ($response->status == 'SUCCESS')
        {
            Tools::redirect($response->redirect_url);
        }
        else
        {
            var_dump($response);
            die('Erro!');
        }
    }
}