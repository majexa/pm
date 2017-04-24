<?php

class PmDnsManagerDevLinux extends PmDnsManagerDevWin {
  
  protected $configFile = '/etc/hosts';

  protected function save() {
    $file = $this->_save();
    PmCore::cmdSuper("cat $file > {$this->configFile}");
  }
  
}