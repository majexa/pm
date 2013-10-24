<?php

class PmRemoteProjectConfig extends PmProjectConfigAbstract {

  protected $serverName;

  function __construct($serverName, $domain) {
    $this->serverName = $serverName;
    parent::__construct($domain);
  }

  function getServerConfig() {
    return O::get('PmRemoteServerConfig', $this->serverName);
  }

}