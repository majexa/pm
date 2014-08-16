<?php

abstract class PmProjectConfigAbstract extends PmConfigAbstract {

  protected $name;

  function __construct($name) {
    $this->name = Misc::checkEmpty($name);
    parent::__construct();
  }

  protected function beforeInit() {
  }

  protected function init() {
    $this->r['name'] = $this->name;
    $this->r['dbName'] = $this->getDbName($this->name);
  }

  function getDbName() {
    return $this->r['name'];
  }

  abstract function serverConfig();

}