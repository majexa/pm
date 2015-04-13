<?php

// dev - означает, что может выполняться только в dev-среде
class PmRemoteServerConfig extends PmServerConfigAbstract {

  public $name;

  function __construct($name) {
    $this->name = $name;
    parent::__construct();
    Arr::checkIsset($this->r, [
      'sshUser'
    ], 'in server config');
  }

  protected function getConfigData() {
    $r = parent::getConfigData();
    $r['ngnEnvPath'] = '/home/user/ngn-env';
    return $r;
  }

  function getFile() {
    return dirname(dirname(__DIR__))."/config/remoteServers/{$this->name}.php";
  }
  
  protected function getName() {
    return $this->name;
  }
  
  protected function getLocalConfig() {
    return (new PmLocalServerConfig());
  }

}
