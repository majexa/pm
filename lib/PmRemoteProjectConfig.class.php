<?php

class PmRemoteProjectConfig extends PmProjectConfigAbstract {

  public $serverName;

  function __construct($serverName, $name) {
    $this->serverName = $serverName;
    parent::__construct($name);
    $this->r['webroot'] = '/home/user/ngn-env/projects/'.$name;
  }

  function serverConfig() {
    return O::get('PmRemoteServerConfig', $this->serverName);
  }

}