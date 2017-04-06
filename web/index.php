<?php

define('PROJECT_KEY', 'pmserver');
define('WEBROOT_PATH', __DIR__);
define('IS_DEBUG', true);
require '../../ngn/init/web-standalone.php';
Lib::addFolder(dirname(__DIR__).'/lib');

print (new DefaultRouter([
  'disableSession' => true
]))->dispatch()->getOutput();

