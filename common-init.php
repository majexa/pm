<?php

define('NGN_PATH', dirname(__DIR__).'/ngn');
require_once NGN_PATH.'/init/core.php';
require_once NGN_PATH.'/init/cli.php';
define('LOGS_PATH', __DIR__.'/logs');
define('DATA_PATH', __DIR__.'/data');
//define('DATA_CACHE', false);
define('PROJECT_KEY', 'pm');

Lib::addFolder(__DIR__.'/lib');

define('NGN_ENV_PATH', dirname(NGN_PATH));
define('TEMP_PATH', NGN_ENV_PATH.'/temp');