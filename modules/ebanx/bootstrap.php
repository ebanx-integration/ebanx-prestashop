<?php

require_once dirname(__FILE__) . '/lib/src/autoload.php';

\Ebanx\Config::set(array(
    'integrationKey' => Configuration::get('EBANX_INTEGRATION_KEY')
  , 'testMode'       => (intval(Configuration::get('EBANX_TESTING')) == 1)
));