<?php

class PmPhpBasicProjectRecords extends ArrayAccesseble {

  function __construct() {
    $this->r = require NGN_ENV_PATH.'/config/phpBasicProjects.php';
    foreach ($this->r as &$v) $v['type'] = 'php-basic';
  }

}