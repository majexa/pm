<?php

class PmDnsManagerDevLinux extends PmDnsManagerDevWin {
  
  protected $configFile = '/etc/hosts';

  protected function save(array $items) {
    $file = $this->_save($items);
    print `sudo cat $file > {$this->configFile}`;
  }
  
}