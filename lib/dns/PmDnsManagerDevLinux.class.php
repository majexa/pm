<?php

class PmDnsManagerDevLinux extends PmDnsManagerDevWin {
  
  protected $configFile = '/etc/hosts';

  protected function save(array $items) {
    $file = $this->_save($items);
    print "BEFORE\n";
    print `sudo cat $file > {$this->configFile}`;
    print "AFTER\n";
  }
  
}