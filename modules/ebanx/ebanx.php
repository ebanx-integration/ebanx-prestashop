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

if (!defined('_PS_VERSION_'))
{
    exit();
}

/**
 * The payment module class
 */
class Ebanx extends PaymentModule
{
    const VERSION = '2.7.1';

    public function __construct()
    {
        $this->name     = 'ebanx';
        $this->tab      = 'payments_gateways';
        $this->version  = self::VERSION;
        $this->author   = 'EBANX';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('EBANX Standard Checkout');
        $this->description = $this->l('EBANX is the market leader in e-commerce payment solutions for International Merchants selling online to Brazil.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('EBANX'))
        {
          $this->warning = $this->l('No name provided');
        }
    }

    /**
     * Uninstalls the EBANX payment gateway
     * @return boolean
     */
    public function uninstall()
    {
        // Delete settings
        if (!Configuration::deleteByName('EBANX_TESTING')
         || !Configuration::deleteByName('EBANX_INTEGRATION_KEY')
         || !parent::uninstall())
        {
                return false;
        }

        return true;
    }

    /**
     * Installs the EBANX payment gateway
     * @return  boolean
     */
    public function install()
    {
        // Applies the changes to all shops (multistore)
        if (Shop::isFeatureActive())
        {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Create the custom orders table to store the hashes
        if (!$this->_createTables()) {
            return false;
        }

        if (!parent::install()
         || !$this->registerHook('payment')
         || !$this->registerHook('paymentReturn')
         || !$this->registerHook('header')
         || !Configuration::updateValue('EBANX_TESTING', true)
         || !Configuration::updateValue('EBANX_INTEGRATION_KEY', ''))
        {
            return false;
        }

        // Create a custom order status
        $status = array(
            'invoice'     => 1
          , 'send_email'  => 0
          , 'module_name' => $this->name
          , 'color'       => 'RoyalBlue'
          , 'unremovable' => 1
          , 'hidden'      => 0
          , 'logable'     => 1
          , 'delivery'    => 0
          , 'shipped'     => 0
          , 'paid'        => 0
          , 'deleted'     => 0
        );

        if (!Db::getInstance()->insert('order_state', $status))
        {
            return false;
        }

        // Setup status translation
        $statusId = (int) Db::getInstance()->Insert_ID();
        $sql = new DbQuery();
        $sql->select('id_lang');
        $sql->from('lang');
        $sql->orderBy('id_lang');
        $sql->where('active = 1');
        $sql->limit('1');
        $langId =  Db::getInstance()->executeS($sql);

        $language = array(
            'id_lang'        => (int) $langId[0]['id_lang']
          , 'id_order_state' => $statusId
          , 'name'           => 'Awaiting EBANX payment'
          , 'template'       => ''
        ,
        );

        if (!Db::getInstance()->insert('order_state_lang', $language))
        {
            return false;
        }

        Configuration::updateValue('EBANX_STATUS_OPEN', $statusId);

        return true;
    }

    /**
     * Adds assets to header
     * @return void
     */
    public function hookHeader()
    {
        Tools::addCSS($this->_path . 'assets/css/app.css', 'all');
        Tools::addJS($this->_path . 'assets/js/app.js');
    }

    /**
     * Creates ebanx_order table to store EBANX transaction hashes
     * @return boolean
     */
    protected function _createTables()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;

        $sql = "
            CREATE TABLE IF NOT EXISTS `{$prefix}ebanx_order` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `hash` varchar(255) NOT NULL,
            `boleto_url` TEXT NOT NULL,
            `payment_method` varchar(255) NOT NULL,
            `order_id` int(10) unsigned NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE = {$engine}
                DEFAULT CHARSET=utf8  auto_increment=1;";

        if (!Db::getInstance()->Execute($sql))
        {
            return false;
        }

        return true;
    }

    public function installDb()
    {
        return true;
    }

    /**
     * Gets the configuration view content
     * @return string
     */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submitebanx'))
        {
            // Returns error array on error
            $isValid = $this->_validateConfiguration();

            if (!is_array($isValid))
            {
                $this->_updateConfiguration();
                $output .= '<div class="module_confirmation conf confirm">'. $this->l('EBANX settings updated.') . '</div>';
            }
            else
            {
                $errors = $isValid;

                foreach ($errors as $error)
                {
                    $output .= '<div class="module_error alert error">' . $error . '</div>';
                }
            }
        }

        $output .= $this->displayForm();

        return $output;
    }

    /**
     * Validates the configuration options
     * @return mixed
     */
    protected function _validateConfiguration()
    {
        $errors = array();

        $testing            = Tools::getValue('EBANX_TESTING');
        $integrationKey     = Tools::getValue('EBANX_INTEGRATION_KEY');

        if (!in_array(intval($testing), array(0, 1)))
        {
            $errors[] = $this->l('Testing mode must be enabled or disabled.');
        }

        $length = strlen($integrationKey);
        if ($length != 100 && $length != 30)
        {
            $errors[] = $this->l('The integration key is not valid.');
        }else if ((strpos($integrationKey, 'test_pk') !== false)){
            $errors[] = $this->l('The integration key is not valid.');
        }

        if(intval($testing)==0 && (strpos($integrationKey, 'test') !== false)){
            $errors[] = $this->l('You are using a Test key in a live environment. Please check your settings.');
        }

        if (count($errors))
        {
            return $errors;
        }

        return true;
    }

    /**
     * Updates EBANX settings
     * @return void
     */
    protected function _updateConfiguration()
    {
        Configuration::updateValue('EBANX_TESTING', intval(Tools::getValue('EBANX_TESTING')));
        Configuration::updateValue('EBANX_INTEGRATION_KEY', Tools::getValue('EBANX_INTEGRATION_KEY'));
    }

    /**
     * Builds the configuration form
     * @return string
     */
    public function displayForm()
    {
        // Get default Language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('EBANX Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Integration key'),
                    'name' => 'EBANX_INTEGRATION_KEY',
                    'size' => 100,
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Test mode'),
                    'name' => 'EBANX_TESTING',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('label' => 'Enabled',  'value' => 1),
                            array('label' => 'Disabled', 'value' => 0)
                        ),
                        'id'   => 'value',
                        'name' => 'label'
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, t    oken apostValind currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['EBANX_INTEGRATION_KEY']     = Configuration::get('EBANX_INTEGRATION_KEY');
        $helper->fields_value['EBANX_TESTING']             = Configuration::get('EBANX_TESTING');

        return $helper->generateForm($fields_form);
    }

    /**
     * Perform payment hook
     * @param array $params
     * @return string
     */
    public function hookPayment($params)
    {
        if (!$this->active)
        {
            return;
        }

        // Defines the base URL with/without HTTPS
        $baseUrl = _PS_BASE_URL_ . __PS_BASE_URI__;

        if (intval(Configuration::get('EBANX_TESTING')) == 0)
        {
            if (intval(Configuration::get('PS_SSL_ENABLED')) == 1)
            {
                $baseUrl = str_replace('http', 'https', $baseUrl);
            }
        }


        $cart     = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $address  = new Address($cart->id_address_invoice);
        $country  = new Country($address->id_country);
        $countries_available =  array('BR', 'MX', 'CO', 'PE', 'CL', 'AR');

        if (Module::isInstalled('ebanxexpress') && $country->iso_code == 'BR') {
            array_shift($countries_available);
        }

        if ($country->iso_code == 'BR') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout.png';
        }elseif ($country->iso_code == 'MX') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout_mx.png';
        }elseif ($country->iso_code == 'CO') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout_co.png';
        }elseif ($country->iso_code == 'PE') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout_pe.png';
        }elseif ($country->iso_code == 'CL') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout_cl.png';
        }elseif ($country->iso_code == 'AR') {
            $imagename = 'modules/ebanx/assets/img/botao_checkout_ar.png';
        }

        $this->context->smarty->assign(
            array(
                'country_code'         => $country->iso_code
              , 'image_checkout'      => __PS_BASE_URI__ . $imagename
              , 'action_checkout'   => $baseUrl . 'index.php?fc=module&module=ebanx&controller=checkout'
              , 'countries_available'   => $countries_available
            )
        );

        return $this->display(__FILE__, 'payment.tpl');
    }

    /**
     * Perform payment return hook
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    /**
     * Get an order status code from Prestashop
     * @param  string $code
     * @return int
     */
    public static function getOrderStatus($code)
    {
        $statuses = array(
            'CA' => 6
          , 'OP' => Configuration::get('EBANX_STATUS_OPEN')
          , 'PE' => Configuration::get('EBANX_STATUS_OPEN')
          , 'CO' => 2
        );

        return $statuses[$code];
    }

    public function saveOrderData($orderId, $hash, $method, $boleto = '')
    {
        $r = Db::getInstance()->insert('ebanx_order', array(
            'hash'       => $hash
          , 'payment_method' => $method
          , 'boleto_url' => $boleto
          , 'order_id'   => $orderId
        ));

        return $r;
    }

    public static function findOrderIdByHash($hash)
    {
        $sql = 'SELECT order_id FROM ' . _DB_PREFIX_ . 'ebanx_order '
             . 'WHERE hash = \'' . $hash . '\'';

        $result = Db::getInstance()->getRow($sql);
        return $result['order_id'];
    }

    public static function findEbanxOrderData($hash)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ebanx_order '
             . 'WHERE hash = \'' . $hash . '\'';

        return Db::getInstance()->getRow($sql);
    }
}
