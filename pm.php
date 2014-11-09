#!/usr/bin/php
<?php

define('PM_PATH', __DIR__);
define('DATA_PATH', __DIR__.'/data');
require __DIR__.'/common-init.php';
Cli::storeCommand(__DIR__.'/logs');
new PmManager($_SERVER['argv']);