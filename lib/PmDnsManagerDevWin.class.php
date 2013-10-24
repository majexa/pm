<?php

class PmDnsManagerDevWin extends PmDnsManagerAbstract {

  protected $configFile = 'C:/Windows/System32/drivers/etc/hosts';

  private $items;

  function create($domain) {
    $items = $this->getItems();
    if (isset($items[$domain])) return;
    $items[$domain] = [
      'host'   => $this->config->r['host'],
      'domain' => $domain
    ];
    $this->save($items);
  }

  protected function getItems() {
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
    return $items;
  }

  protected function save(array $items) {
    $c = file_get_contents($this->configFile);
    preg_match_all('/^(?=[#\s]).*$/m', $c, $m);
    $lines = $m[0];
    foreach ($items as $v) $lines[] = $v['host'].'     '.$v['domain'];
    file_put_contents($this->configFile, implode("\n", $lines));
  }

  function delete($domain) {
    $items = $this->getItems();
    unset($items[$domain]);
    $this->save($items);
  }

  function rename($oldDomain, $newDomain) {
    $items = $this->getItems();
    unset($items[$oldDomain]);
    $items[$newDomain] = [
      'host'   => $this->config->r['host'],
      'domain' => $newDomain
    ];
    $this->save($items);
  }

}