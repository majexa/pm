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
      if (isset($v['aliases']))
        foreach ($v['aliases'] as $alias) $this->create($alias);
    }
  }

  abstract function create($domain);
 
  abstract protected function getItems();
  
  abstract protected function save(array $items);
  
  abstract function rename($oldDomain, $newDomain);
  
  abstract function delete($domain);
  
}
