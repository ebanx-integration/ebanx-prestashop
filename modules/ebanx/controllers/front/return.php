<?php

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

class EbanxReturnModuleFrontController extends ModuleFrontController
{
  public function init()
  {
    parent::init();

    $cartId = (int) Tools::getValue('merchant_payment_code', 0);
    $hash   = Tools::getValue('hash');

    $response   = \Ebanx\Ebanx::doQuery(array('hash' => $hash));

    $status = Ebanx::getOrderStatus($response->payment->status);

    $order = new Order(Order::getOrderByCartId($cartId));
    $order->setCurrentState($status);

    $redirectLink = 'index.php?controller=history&id_order' . $order->reference;

    Tools::redirect($redirectLink);
  }
}