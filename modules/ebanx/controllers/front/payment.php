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

        $maxInstallments = intval(Configuration::get('EBANX_INSTALLMENTS_NUMBER'));

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

        if (($totalReal / 20) < $maxInstallments)
        {
          $maxInstallments = floor($totalReal / 20);
        }

        $currency = new Currency($this->context->cart->id_currency);

        $smarty->assign(array(
            'action_url'          => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=ebanx&controller=checkout'
          , 'total'               => $total
          , 'image'               => __PS_BASE_URI__ . 'modules/ebanx/assets/img/ebanx.png'
          , 'total_installments'  => $totalInstallments
          , 'enable_installments' => (intval(Configuration::get('EBANX_INSTALLMENTS_ACTIVE')) == 1)
          , 'max_installments'    => $maxInstallments
          , 'currency_code'       => $this->context->currency->iso_code
        ));

        $this->setTemplate('form.tpl');
    }
}