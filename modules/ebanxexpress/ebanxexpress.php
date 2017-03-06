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
class EbanxExpress extends PaymentModule
{
    public function __construct()
    {
        $this->name     = 'ebanxexpress';
        $this->tab      = 'payments_gateways';
        $this->version  = '2.6.0';
        $this->author   = 'EBANX';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('EBANX Express');
        $this->description = $this->l('EBANX is the market leader in e-commerce payment solutions for International Merchants selling online to Brazil.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('EBANX_EXPRESS'))
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
        if (!Configuration::deleteByName('EBANX_EXPRESS_TESTING')
         || !Configuration::deleteByName('EBANX_EXPRESS_INTEGRATION_KEY')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_ACT')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_NUM')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_MOD')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_2')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_3')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_4')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_5')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_6')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_7')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_8')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_9')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_10')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_11')
         || !Configuration::deleteByName('EBANX_EXPRESS_INSTALLMENTS_INT_12')
         || !Configuration::deleteByName('EBANX_EXPRESS_STATUS_OPEN')
         || !Configuration::deleteByName('EBANX_ENABLE_BOLETO')
         || !Configuration::deleteByName('EBANX_ENABLE_CREDITCARD')
         || !Configuration::deleteByName('EBANX_ENABLE_TEF')
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

        // Create the custom orders table to store the hashes
        if (!$this->_createTables()) {
            return false;
        }

        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('header')
            || !Configuration::updateValue('EBANX_EXPRESS_TESTING', true)
            || !Configuration::updateValue('EBANX_EXPRESS_INTEGRATION_KEY', '')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_ACT', false)
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_NUM', 1)
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_MOD', 'simple')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_2', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_3', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_4', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_5', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_6', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_7', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_8', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_9', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_10', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_11', '0.00')
            || !Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_12', '0.00')
            || !Configuration::updateValue('EBANX_ENABLE_CREDITCARD', false)
            || !Configuration::updateValue('EBANX_ENABLE_BOLETO', true)
            || !Configuration::updateValue('EBANX_ENABLE_TEF', true))
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

        Configuration::updateValue('EBANX_EXPRESS_STATUS_OPEN', $statusId);

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
            DROP TABLE IF EXISTS `{$prefix}ebanx_order`;
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

        if (Tools::isSubmit('submitebanxexpress'))
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

        $testing            = Tools::getValue('EBANX_EXPRESS_TESTING');
        $integrationKey     = Tools::getValue('EBANX_EXPRESS_INTEGRATION_KEY');
        $installmentsActive = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_ACT');
        $installmentsNumber = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_NUM');
        $interestRate[0]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_2');
        $interestRate[1]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_3');
        $interestRate[2]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_4');
        $interestRate[3]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_5');
        $interestRate[4]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_6');
        $interestRate[5]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_7');
        $interestRate[6]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_8');
        $interestRate[7]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_9');
        $interestRate[8]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_10');
        $interestRate[9]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_11');
        $interestRate[10]       = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_12');
        $installmentsMode   = Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_MOD');

        if (!in_array(intval($testing), array(0, 1)))
        {
            $errors[] = $this->l('Testing mode must be enabled or disabled.');
        }
        $length = strlen($integrationKey);
        if ($length != 100 && $lenght != 30)
        {
            $errors[] = $this->l('The integration key is not valid.');
        }

        if (!in_array(intval($installmentsActive), array(0, 1)))
        {
            $errors[] = $this->l('Installments must be enabled or disabled.');
        }

        if (!in_array(intval($installmentsNumber), range(1, 12)))
        {
            $errors[] = $this->l('The maximum installments number must be between 1 and 12.');
        }

        for ($i=0; $i < 11; $i++) {
            if (!is_numeric($interestRate[$i]))
            {
                $errors[] = $this->l('The interest rate must be a number.');
                break;
            }
        }

        if (!in_array(intval($installmentsMode), array('simple', 'compound')))
        {
            $errors[] = $this->l('The interest calculation must be either simple or compound.');
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
        Configuration::updateValue('EBANX_EXPRESS_TESTING', intval(Tools::getValue('EBANX_EXPRESS_TESTING')));
        Configuration::updateValue('EBANX_EXPRESS_INTEGRATION_KEY', Tools::getValue('EBANX_EXPRESS_INTEGRATION_KEY'));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_ACT', intval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_ACT')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_NUM', intval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_NUM')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_2', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_2')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_3', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_3')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_4', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_4')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_5', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_5')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_6', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_6')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_7', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_7')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_8', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_8')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_9', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_9')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_10', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_10')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_11', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_11')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_INT_12', floatval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_INT_12')));
        Configuration::updateValue('EBANX_EXPRESS_INSTALLMENTS_MOD', strval(Tools::getValue('EBANX_EXPRESS_INSTALLMENTS_MOD')));
        Configuration::updateValue('EBANX_ENABLE_BOLETO', intval(Tools::getValue('EBANX_ENABLE_BOLETO')));
        Configuration::updateValue('EBANX_ENABLE_CREDITCARD', intval(Tools::getValue('EBANX_ENABLE_CREDITCARD')));
        Configuration::updateValue('EBANX_ENABLE_TEF', intval(Tools::getValue('EBANX_ENABLE_TEF')));
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
                    'name' => 'EBANX_EXPRESS_INTEGRATION_KEY',
                    'size' => 100,
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Test mode'),
                    'name' => 'EBANX_EXPRESS_TESTING',
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
                    'label' => $this->l('Enable boleto payments'),
                    'name' => 'EBANX_ENABLE_BOLETO',
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
                    'label' => $this->l('Enable TEF payments'),
                    'name' => 'EBANX_ENABLE_TEF',
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
                    'label' => $this->l('Enable credit card payments'),
                    'name' => 'EBANX_ENABLE_CREDITCARD',
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
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_ACT',
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
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_NUM',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('label' => '1', 'value' => 1),
                            array('label' => '2', 'value' => 2),
                            array('label' => '3', 'value' => 3),
                            array('label' => '4', 'value' => 4),
                            array('label' => '5', 'value' => 5),
                            array('label' => '6', 'value' => 6),
                            array('label' => '7', 'value' => 7),
                            array('label' => '8', 'value' => 8),
                            array('label' => '9', 'value' => 9),
                            array('label' => '10', 'value' => 10),
                            array('label' => '11', 'value' => 11),
                            array('label' => '12', 'value' => 12),
                        ),
                        'id'   => 'value',
                        'name' => 'label'
                    )
                ),
                array(
                   'type' => NULL,
                   'label' => $this->l('Interest rate for each installments:'),
                   'name' => NULL,
                   'desc' => 'We recomend you to use the values of your agreement.'
               ),
                array(
                    'type' => 'text',
                    'label' => $this->l('2'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_2',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('3'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_3',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('4'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_4',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('5'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_5',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('6'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_6',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('7'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_7',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('8'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_8',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('9'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_9',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('10'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_10',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('11'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_11',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('12'),
                    'name' => 'EBANX_EXPRESS_INSTALLMENTS_INT_12',
                    'size' => 4,
                    'suffix' => '%',
                    'required' => true
                ),

                // array(
                //     'type' => 'text',
                //     'label' => $this->l('Installments interest rate (%)'),
                //     'name' => 'EBANX_EXPRESS_INSTALLMENTS_MOD',
                //     'size' => 10,
                //     'required' => false
                // ),
                // array(
                //     'type' => 'select',
                //     'label' => $this->l('Installments interest calculation mode'),
                //     'name' => 'EBANX_EXPRESS_INSTALLMENTS_MOD',
                //     'options' => array(
                //         'query' => array(
                //             array('label' => 'Compound', 'value' => 'simple'),
                //             array('label' => 'Simple', 'value' => 'compound')
                //         ),
                //         'id'   => 'value',
                //         'name' => 'label'
                //     ),
                //     'required' => false
                // ),
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
        $helper->fields_value['EBANX_EXPRESS_INTEGRATION_KEY']     = Configuration::get('EBANX_EXPRESS_INTEGRATION_KEY');
        $helper->fields_value['EBANX_EXPRESS_TESTING']             = Configuration::get('EBANX_EXPRESS_TESTING');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_ACT'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_ACT');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_NUM'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_NUM');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_2'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_2');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_3'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_3');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_4'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_4');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_5'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_5');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_6'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_6');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_7'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_7');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_8'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_8');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_9'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_9');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_10'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_10');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_11'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_11');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_INT_12'] = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_12');
        $helper->fields_value['EBANX_EXPRESS_INSTALLMENTS_MOD']   = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_MOD');
        $helper->fields_value['EBANX_ENABLE_BOLETO']       = Configuration::get('EBANX_ENABLE_BOLETO');
        $helper->fields_value['EBANX_ENABLE_CREDITCARD']   = Configuration::get('EBANX_ENABLE_CREDITCARD');
        $helper->fields_value['EBANX_ENABLE_TEF']          = Configuration::get('EBANX_ENABLE_TEF');

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

        if (intval(Configuration::get('EBANX_EXPRESS_TESTING')) == 0)
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

        $this->context->smarty->assign(
            array(
                'action_url_boleto' => $baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=payment&method=boleto'
              , 'image_boleto'      => __PS_BASE_URI__ . 'modules/ebanxexpress/assets/img/boleto.png'
              , 'action_url_cc'     => $baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=payment&method=creditcard'
              , 'image_cc'          => __PS_BASE_URI__ . 'modules/ebanxexpress/assets/img/creditcard.png'
              , 'action_url_tef'    => $baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=payment&method=tef'
              , 'image_tef'         => __PS_BASE_URI__ . 'modules/ebanxexpress/assets/img/tef.png'
              , 'ebanx_boleto_enabled' => intval(Configuration::get('EBANX_ENABLE_BOLETO')) == 1
              , 'ebanx_cc_enabled'     => intval(Configuration::get('EBANX_ENABLE_CREDITCARD')) == 1
              , 'ebanx_tef_enabled'    => intval(Configuration::get('EBANX_ENABLE_TEF')) == 1
              , 'country_code'         => $country->iso_code
              , 'action_checkout'   => $baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=checkout'
            )
        );
        // var_dump($this->context->smarty->assign['ebanx_boleto_enabled']);
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
          , 'OP' => Configuration::get('EBANX_EXPRESS_STATUS_OPEN')
          , 'PE' => Configuration::get('EBANX_EXPRESS_STATUS_OPEN')
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

    public static function calculateTotalWithInterest($orderTotal, $installments)
    {
        switch ($installments) {
          case '1':
            return $orderTotal;
            break;
          case '2':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_2');
            break;
          case '3':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_3');
            break;
          case '4':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_4');
            break;
          case '5':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_5');
            break;
          case '6':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_6');
            break;
          case '7':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_7');
            break;
          case '8':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_8');
            break;
          case '9':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_9');
            break;
          case '10':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_10');
            break;
          case '11':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_11');
            break;
          case '12':
            $interest_rate = Configuration::get('EBANX_EXPRESS_INSTALLMENTS_INT_12');
            break;
          default:
            # code...
            break;
        }
        $total = (floatval($interest_rate / 100) * floatval($orderTotal) + floatval($orderTotal));

        return $total;
    }
}
