<?php

define('PROJECT_KEY', 'pmserver');
define('WEBROOT_PATH', __DIR__);
define('PM_PATH', dirname(__DIR__));
define('IS_DEBUG', true);
define('BUILD_MODE', true);
require '../../ngn/init/web-standalone.php';
Lib::addFolder(dirname(__DIR__).'/lib');
R::set('disableOutput', true);

print (new DefaultRouter([
  'disableSession' => true
]))->dispatch()->getOutput();

