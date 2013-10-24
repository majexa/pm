<?php

class PmDnsManager {

  /**
   * @return PmDnsManagerAbstract
   */
  static function get() {
    $config = new PmLocalServerConfig();
    return O::get('PmDnsManager'.(isset($config['dnsMasterHost']) ? 'Bind' : 'Dummy'));
  }

}