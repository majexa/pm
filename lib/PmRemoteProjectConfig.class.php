<?php

class PmRemoteProjectConfig extends PmProjectConfigAbstract {

  protected $serverName;

  function __construct($serverName, $name) {
    $this->serverName = $serverName;
    parent::__construct($name);
  }

  function serverConfig() {
    return O::get('PmRemoteServerConfig', $this->serverName);
  }

}