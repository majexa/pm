<?php

/**
 * Existing ngn type independent project config
 */
class PmProjectConfig extends PmProjectConfigAbstract {

  function serverConfig() {
    return O::get('PmLocalServerConfig');
  }

  protected function init() {
    $this->r = array_merge($this->r, $this->serverConfig()->r);
    $this->r['vhostTttt'] = St::tttt($this->r['vhostTttt'], $this->r);
    $this->r['webroot'] = St::tttt($this->r['webroot'], $this->r);
  }

  function isNgnProject() {
    return !file_exists($this->r['webroot'].'/.nonNgn');
  }

}
