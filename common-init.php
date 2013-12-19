<?php

define('NGN_PATH', dirname(__DIR__).'/ngn');
define('SITE_PATH', __DIR__);
define('PROJECT_KEY', 'pm');
require_once NGN_PATH.'/init/more.php';
require_once NGN_PATH.'/init/cli.php';

//define('LOGS_PATH', __DIR__.'/logs');
//define('DATA_PATH', __DIR__.'/data');
Lib::addFolder(__DIR__.'/lib');

define('NGN_ENV_PATH', dirname(NGN_PATH));
//define('TEMP_PATH', NGN_ENV_PATH.'/temp');