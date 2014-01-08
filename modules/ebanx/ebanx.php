<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author Ebanx
 *  @copyright  Ebanx 2013
 */

if (!defined('_PS_VERSION_'))
{
    exit();
}

class Ebanx extends PaymentModule
{
    public function __construct()
    {
        $this->name     = 'ebanx';
        $this->tab      = 'payments_gateways';
        $this->version  = '1.0.0';
        $this->author   = 'EBANX';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('EBANX');
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
         || !Configuration::deleteByName('EBANX_INSTALLMENTS_ACTIVE')
         || !Configuration::deleteByName('EBANX_INSTALLMENTS_NUMBER')
         || !Configuration::deleteByName('EBANX_INTEREST_RATE')
         || !Configuration::deleteByName('EBANX_STATUS_OPEN')
         || !parent::uninstall())
        {
                return false;
        }

        // Delete custom order status
        // if (!Db::getInstance()->delete('order_state', "module_name = 'ebanx'"))
        // {
        //     return false;
        // }

        // Delete custom order status translations
        // if (!Db::getInstance()->delete('order_state_lang', "name LIKE '%EBANX%'"))
        // {
        //     return false;
        // }


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

        if (!$this->_createTables()) {
            return false;
        }

        if (!parent::install()
         || !$this->registerHook('payment')
         || !$this->registerHook('paymentReturn')
         || !Configuration::updateValue('EBANX_TESTING', true)
         || !Configuration::updateValue('EBANX_INTEGRATION_KEY', '')
         || !Configuration::updateValue('EBANX_INSTALLMENTS_ACTIVE', false)
         || !Configuration::updateValue('EBANX_INSTALLMENTS_NUMBER', 6)
         || !Configuration::updateValue('EBANX_INTEREST_RATE', 10.0))
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
        $language = array(
            'id_lang'        => 1
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
     * Creates ebanx_order table to store EBANX transaction hashes
     * @return boolean
     */
    protected function _createTables()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;

        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ebanx_order` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `hash` varchar(255) NOT NULL,
            `order_id` int(10) unsigned NOT NULL ,
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
        $installmentsActive = Tools::getValue('EBANX_INSTALLMENTS_ACTIVE');
        $installmentsNumber = Tools::getValue('EBANX_INSTALLMENTS_NUMBER');
        $interestRate       = Tools::getValue('EBANX_INTEREST_RATE');

        if (!in_array(intval($testing), array(0, 1)))
        {
            $errors[] = $this->l('Testing mode must be enabled or disabled.');
        }

        if (strlen($integrationKey) != 100)
        {
            $errors[] = $this->l('The integration key is not valid.');
        }

        if (!in_array(intval($installmentsActive), array(0, 1)))
        {
            $errors[] = $this->l('Installments must be enabled or disabled.');
        }

        if (!in_array(intval($installmentsNumber), range(1, 6)))
        {
            $errors[] = $this->l('The maximum installments number must be between 1 and 6.');
        }

        if (!is_numeric($interestRate))
        {
            $errors[] = $this->l('The interest rate must be a number.');
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
        Configuration::updateValue('EBANX_INSTALLMENTS_ACTIVE', intval(Tools::getValue('EBANX_INSTALLMENTS_ACTIVE')));
        Configuration::updateValue('EBANX_INSTALLMENTS_NUMBER', intval(Tools::getValue('EBANX_INSTALLMENTS_NUMBER')));
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
                array(
                    'type' => 'select',
                    'label' => $this->l('Installments'),
                    'name' => 'EBANX_INSTALLMENTS_ACTIVE',
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
                array(
                    'type' => 'select',
                    'label' => $this->l('Maximum installments number'),
                    'name' => 'EBANX_INSTALLMENTS_NUMBER',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('label' => '1', 'value' => 1),
                            array('label' => '2', 'value' => 2),
                            array('label' => '3', 'value' => 3),
                            array('label' => '4', 'value' => 4),
                            array('label' => '5', 'value' => 5),
                            array('label' => '6', 'value' => 6),
                        ),
                        'id'   => 'value',
                        'name' => 'label'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Installments interest rate (%)'),
                    'name' => 'EBANX_INTEREST_RATE',
                    'size' => 10,
                    'required' => false
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
        $helper->fields_value['EBANX_INSTALLMENTS_ACTIVE'] = Configuration::get('EBANX_INSTALLMENTS_ACTIVE');
        $helper->fields_value['EBANX_INSTALLMENTS_NUMBER'] = Configuration::get('EBANX_INSTALLMENTS_NUMBER');
        $helper->fields_value['EBANX_INTEREST_RATE']       = Configuration::get('EBANX_INTEREST_RATE');

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

        $this->context->smarty->assign(
            array(
                'action_url' => 'index.php?fc=module&module=ebanx&controller=payment',
                'image' => __PS_BASE_URI__ . 'modules/ebanx/assets/img/logo.png'
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

    public function saveHash($orderId, $hash)
    {
        $r = Db::getInstance()->insert('ebanx_order', array(
            'hash'     => $hash
          , 'order_id' => $orderId
        ));

        return $r;
    }

    public static function findOrderIdByHash($hash)
    {
        $sql = 'SELECT order_id FROM ' . _DB_PREFIX_ . 'ebanx_order '
             . 'WHERE hash = \'' . $hash . '\'';
             var_dump($sql);
        $result = Db::getInstance()->getRow($sql);
        return $result['order_id'];
    }
}
