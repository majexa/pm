<?php

// ngn init
define('NGN_PATH', dirname(dirname(__DIR__)).'/ngn');
define('VENDORS_PATH', dirname(dirname(__DIR__)).'/vendors');

// web init
define('NGN_ENV_PATH', dirname(NGN_PATH));
define('SITE_PATH', __DIR__.'/site');
define('WEBROOT_PATH', __DIR__);
define('IS_DEBUG', true);
define('PROJECT_KEY', 'pm');
define('CACHE_DATA', false);
define('DISABLE_FANCY_UPLOAD', true);
define('OUTPUT_DISABLE', true);

if (!defined('DEBUG_STATIC_FILES')) define('DEBUG_STATIC_FILES', false);
if (!defined('FORCE_STATIC_FILES_CACHE')) define('FORCE_STATIC_FILES_CACHE', false);

require_once NGN_PATH.'/init/core.php';
require_once(NGN_PATH.'/init/web.php');

setConstant('SITE_LIB_PATH', SITE_PATH.'/lib');

// pm init
Lib::addFolder(dirname(__DIR__).'/lib');
