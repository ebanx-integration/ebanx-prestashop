<?php

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

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

/**
 * The return action controller. Updates the order status after being redirected
 * from EBANX.
 */
class EbanxExpressPlugincheckModuleFrontController extends ModuleFrontController
{
	public function init()
	{
		parent::init();
		$plugincheck_list = array(
			'php'              => phpversion(),
			'sql'              => $this->getSQLVersion(),
			'prestashop'       => _PS_VERSION_,
			'ebanx-prestashop' => EbanxExpress::VERSION,
			'configs'          => $this->getConfigs(),
			'plugins'          => $this->getModuleList(),
		);
		echo json_encode($plugincheck_list);
	}

	private function getSQLVersion() {
		return Db::getInstance()->getVersion();
	}

	private function getConfigs() {
		$config_list = array(
			'EBANX_EXPRESS_TESTING' => Configuration::get('EBANX_EXPRESS_TESTING') === 1,
			'EBANX_EXPRESS_INSTALLMENTS_ACT' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_ACT'),
			'EBANX_EXPRESS_INSTALLMENTS_NUM' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_NUM'),
			'EBANX_EXPRESS_INSTALLMENTS_MOD' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_MOD'),
			'EBANX_EXPRESS_INSTALLMENTS_INTEREST' => array(
				'1' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT'),
				'2' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_2'),
				'3' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_3'),
				'4' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_4'),
				'5' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_5'),
				'6' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_6'),
				'7' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_7'),
				'8' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_8'),
				'9' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_9'),
				'10' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_10'),
				'11' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_11'),
				'12' => Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_12'),
			),
			'EBANX_EXPRESS_STATUS_OPEN' => Configuration::get('EBANX_EXPRESS_STATUS_OPEN'),
			'EBANX_ENABLE_BOLETO' => Configuration::get('EBANX_ENABLE_BOLETO') === 1,
			'EBANX_ENABLE_CREDITCARD' => Configuration::get('EBANX_ENABLE_CREDITCARD') === 1,
			'EBANX_ENABLE_TEF' => Configuration::get('EBANX_ENABLE_TEF') === 1,
		);
		return $config_list;
	}

	private function getModuleList() {
		$plugin_list = Db::getInstance()->executeS('SELECT name, version, active FROM ps_module;');
		$formated_plugin_list = array();
		foreach ($plugin_list as $plugin){
			array_push($formated_plugin_list, array(
				$plugin['name'] => array(
					'version' => $plugin['version'],
					'active'  => $plugin['active'] === '1',
				)
			));
		}
		return $formated_plugin_list;
	}
}
