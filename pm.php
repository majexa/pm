VENDORS_PATH#!/usr/bin/php
<?php

require __DIR__.'/common-init.php';
Cli::storeCommand(__DIR__.'/logs');
new PmManager($_SERVER['argv']);