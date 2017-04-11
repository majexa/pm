<?php

abstract class PmWebserverAbstract {

  /**
   * @var PmLocalServerConfig
   */
  protected $config;

  function __construct() {
    $this->config = new PmLocalServerConfig;
  }

  function restart() {
    $k = $this->config['os'] == 'win' ? ' -k' : '';
    Misc::checkEmpty($this->config->r['webserverP'], 'webserver path "webserverP" must be defined in server config');
    PmCore::cmdSuper("'{$this->config->r['webserverP']}'$k restart");
  }

  function regen() {
    O::get('PmRecords')->remove();
    O::get('PmRecords')->save();
    return $this;
  }

  // ---------------------------------------------------------------------------

  abstract protected function renderVhostAlias($location, $alias);

  function delete($name) {
    File::delete("{$this->config['webserverProjectsConfigFolder']}/$name");
    return $this;
  }

}