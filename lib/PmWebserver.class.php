<?php

class PmWebserver {

  /**
   * @return PmWebserverAbstract
   */
  static function get() {
    $class = 'PmWebserver'.ucfirst((new PmLocalServerConfig())->r['webserver']);
    return O::get($class);
  }

}