<?php

abstract class PmDnsManagerAbstract {

  protected $config;

  function __construct() {
    $this->config = new PmLocalServerConfig;
    $this->init();
  }
  
  protected function init() {}
  
  function regen(array $records) {
    foreach ($records as $v) {
      $this->create($v['domain']);
      if (isset($v['aliases'])) {
        foreach ($v['aliases'] as $alias) $this->createAndSave($alias);
      }
    }
    $this->save();
  }

  abstract function create($domain);

  function createAndSave($domain) {
    $this->create($domain);
    $this->save();
  }

  abstract protected function getItems();
  
  abstract protected function save();
  
  abstract function rename($oldDomain, $newDomain);
  
  abstract function delete($domain);
  
}
