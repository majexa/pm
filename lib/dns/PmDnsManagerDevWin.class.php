<?php

abstract class PmDnsManagerDevWin extends PmDnsManagerAbstract {

  protected $configFile = 'C:/Windows/System32/drivers/etc/hosts';

  function create($domain) {
    $items = $this->getItems();
    if (isset($items[$domain])) return;
    $items[$domain] = [
      'host'   => $this->config->r['host'],
      'domain' => $domain
    ];
    $this->_items = $items;
  }

  protected $_items;

  protected function getItems() {
    if (isset($this->_items)) return $this->_items;
    if (!$c = file_get_contents($this->configFile)) error('File "'.$this->configFile.'" not exists');
    $items = [];
    preg_match_all('/^(?![#\s]).*$/m', $c, $m);
    foreach ($m[0] as $v) {
      preg_match('/^(\S+)\s+(\S*)/', $v, $m2);
      if (empty($m2[2])) continue;
      $items[$m2[2]] = [
        'host'   => $m2[1],
        'domain' => $m2[2]
      ];
    }
    return $this->_items = $items;
  }

  protected function _save() {
    $c = file_get_contents($this->configFile);
    preg_match_all('/^(?=[#\s]).*$/m', $c, $m);
    $lines = $m[0];
    foreach ($this->_items as $v) $lines[] = $v['host'].'     '.$v['domain'];
    file_put_contents(TEMP_PATH.'/hosts', implode("\n", $lines));
    return TEMP_PATH.'/hosts';
  }

  function delete($domain) {
    unset($this->_items[$domain]);
    $this->save();
  }

  function rename($oldDomain, $newDomain) {
    unset($this->_items[$oldDomain]);
    $this->_items[$newDomain] = [
      'host'   => $this->config->r['host'],
      'domain' => $newDomain
    ];
    $this->save();
  }

}