<?php

// dev - означает, что может выполняться только в dev-среде
class PmLocalServerConfigDev extends PmLocalServerConfig {

  function __construct() {
    parent::__construct();
    if ($this->r['sType'] != 'dev')
      throw new Exception('Object allowed only for dev servers');
  }

}
