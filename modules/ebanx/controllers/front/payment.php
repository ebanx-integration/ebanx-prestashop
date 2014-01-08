<?php

class EbanxPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();

        global $smarty;

        $total    = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $interest = floatval(Configuration::get('EBANX_INTEREST_RATE'));
        $totalInstallments = ($total * (100 + $interest)) / 100.0;

        $currency = new Currency($this->context->cart->id_currency);

        $smarty->assign(array(
            'action_url'          => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=ebanx&controller=checkout'
          , 'total'               => $total
          , 'image'               => __PS_BASE_URI__ . 'modules/ebanx/assets/img/ebanx.png'
          , 'total_installments'  => $totalInstallments
          , 'enable_installments' => (intval(Configuration::get('EBANX_INSTALLMENTS_ACTIVE')) == 1)
          , 'max_installments'    => intval(Configuration::get('EBANX_INSTALLMENTS_NUMBER'))
          , 'currency_code'       => $currency->iso_code
        ));

        $this->setTemplate('form.tpl');
    }
}