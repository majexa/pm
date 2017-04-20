<?php

abstract class PmProjectConfigAbstract extends PmConfigAbstract {

  protected $name;

  function __construct($name) {
    $this->r['name'] = $this->name = Misc::checkEmpty(Misc::checkString($name, true));
    $this->r['dbName'] = $this->name;
    parent::__construct();
  }

  function getDbName() {
    return $this->r['dbName'];
  }

  abstract function serverConfig();

}