<?php

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

class EbanxNotifyModuleFrontController extends ModuleFrontController
{
  public function init()
  {
    parent::init();

    $hashes = Tools::getValue('hash_codes');

    if (is_array($hash))
    {
      foreach ($hashes as $hash)
      {
        $this->_updateOrder($hash);
      }
    }
    else
    {
      $this->_updateOrder($hashes);
    }

    echo 'OK!';
    exit();
  }

  protected function _updateOrder($hash)
  {
      $response = \Ebanx\Ebanx::doQuery(array('hash' => $hash));

      $status = Ebanx::getOrderStatus($response->payment->status);

      $order = new Order(Ebanx::findOrderIdByHash($hash));
      $order->setCurrentState($status);
  }
}