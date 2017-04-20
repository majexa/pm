<?php

class PmLocalProjectConfig extends PmProjectConfigAbstract {

  function serverConfig() {
    return new PmLocalServerConfig;
  }

  function getConstant($type, $name) {
    return Config::getConstant("{$this->r['webroot']}/site/config/constants/$type.php", $name, true);
  }

  protected function init() {
    $this->r = array_merge($this->r, $this->serverConfig()->r);
    $this->r['vhostTttt'] = St::tttt($this->r['vhostTttt'], $this->r);
    $this->r['webroot'] = St::tttt($this->r['webroot'], $this->r);
  }

  function isNgnProject() {
    return !file_exists($this->r['webroot'].'/.nonNgn');
  }

  protected $multipleParams = ['vhostAliases', 'afterCmdTttt'];

}
