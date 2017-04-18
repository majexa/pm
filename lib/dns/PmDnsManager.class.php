<?php

class PmDnsManager {

  /**
   * @return PmDnsManagerAbstract
   */
  static function factory() {
    $config = new PmLocalServerConfig;
    if ($config['dns']) {
      $name = ucfirst($config['dns']);
    } elseif (isset($config['dnsMasterHost'])) {
      $name = 'Bind';
    } else {
      $name = 'Dummy';
    }
    return O::get('PmDnsManager'.$name);
  }

}