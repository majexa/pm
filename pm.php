#!/usr/bin/php
<?php

die2(__DIR__);

define('PROJECT_PATH', __DIR__);
define('PM_PATH', __DIR__);
define('PROJECT_KEY', 'pm');
require __DIR__.'/common-init.php';
Cli::storeCommand(__DIR__.'/logs');
new PmManager($_SERVER['argv']);