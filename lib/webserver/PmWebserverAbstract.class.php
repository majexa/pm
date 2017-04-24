<?php

abstract class PmWebserverAbstract {

  /**
   * @var PmLocalServerConfig
   */
  protected $config;

  function __construct() {
    $this->config = O::get('PmLocalServerConfig');
  }

  function restart() {
    $k = $this->config['os'] == 'win' ? ' -k' : '';
    Misc::checkEmpty($this->config->r['webserverP'], 'webserver path "webserverP" must be defined in server config');
    PmCore::cmdSuper("'{$this->config->r['webserverP']}'$k restart");
  }

  function regen() {
    return $this;
  }

}