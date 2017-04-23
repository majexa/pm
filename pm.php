#!/usr/bin/php
<?php

print_r(glob(dirname(__DIR__).'/*'));
die();

define('PROJECT_PATH', __DIR__);
define('PM_PATH', __DIR__);
define('PROJECT_KEY', 'pm');
require __DIR__.'/common-init.php';
Cli::storeCommand(__DIR__.'/logs');
new PmManager($_SERVER['argv']);