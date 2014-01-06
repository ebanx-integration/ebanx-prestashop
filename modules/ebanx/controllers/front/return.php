<?php

class EbanxReturnModuleFrontController extends ModuleFrontController
{
  public function init()
  {
    parent::init();

    $cartId = (int) Tools::getValue('merchant_payment_code', 0);
    $hash = Tools::getValue('hash');

    $orderId = Order::getOrderByCartId($cartId);
    $order = new Order($orderId);

    $redirectLink = 'index.php?controller=history&id_order' . $order->reference;

    Tools::redirect($redirectLink);
  }
}