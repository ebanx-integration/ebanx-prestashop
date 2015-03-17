<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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


require 'config/config.inc.php';
require 'config/autoload.php';
require_once 'modules/ebanx/bootstrap.php';
require 'modules/ebanx/ebanx.php';

/**
 * The notify action controller. It's called by the EBANX robot when the payment
 * is updated.
 */

$hashes = explode(',', $_REQUEST['hash_codes']);
$notification_type = $_REQUEST['notification_type'];

foreach ($hashes as $hash)
{
    $response = \Ebanx\Ebanx::doQuery(array('hash' => $hash));

    if ($response->status == 'ERROR')
    {
        echo "Error contacting EBANX";
    }

    elseif ($notification_type == 'chargeback')
    {
        echo "payment was not updated due to chargeback.";
    }

    elseif ($notification_type == 'refund')
    {
        echo "payment was not updated due to refund.";
    }

    else
    {
        $type = $response->payment->payment_type_code;

        $status  = Ebanx::getOrderStatus($response->payment->status);

        $orderId = Ebanx::findOrderIdByHash($hash);
                
        $order = new Order($orderId);
        
        $order->setCurrentState($status);

        echo 'OK: ' . $hash . ' <br>';
    }
}