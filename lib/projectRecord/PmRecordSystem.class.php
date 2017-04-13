<?php

class PmRecordSystem extends PmRecord {

  function __construct(array $record) {
    Arr::checkEmpty($record, 'name');
    parent::__construct($record);
    $this->r['domain'] = $this->domain();
  }

  protected function domain() {
    if ($this->r['name'] == 'dns') {
      return $this->r['name'].'.'.PmCore::getLocalConfig()['dnsBaseDomain'];
    }
    return $this->r['name'].'.'.PmCore::getLocalConfig()['baseDomain'];
  }

  protected function _getVhostFolder() {
    return $this->config['webserverSystemConfigFolder'];
  }

  protected function getVhostRecord() {
    if (isset($this->config[$this->r['name'].'VhostTttt'])) {
      $tplName = $this->r['name'].'VhostTttt';
      $record = array_merge(['domain' => $this->r['domain']], $this->config->r);
    }
    else {
      $tplName = 'abstractVhostTttt';
      $record = array_merge($this->config->r, [
        'domain'  => $this->r['domain'],
        'webroot' => PmCore::getSystemWebFolders()[$this->r['name']],
        'end'     => ''
      ]);
    }
    $record['rootLocation'] = '';
    $record['end'] = <<<RECORD

  location /i/ {
    access_log    off;
    expires       30d;
    add_header    Cache-Control public;
    root    /home/user/ngn-env/ngn;
  }

RECORD;
    return $this->renderVhostRecord($this->config[$tplName], $record);
  }

  function saveRecord() {
    throw new Exception(
      'System records are received by core method PmCore::getSystemWebFolders() and can`t be saved.');
  }

  function getRecords() {
    $records = [];
    foreach (array_keys(PmCore::getSystemWebFolders()) as $name) {
      $records[] = [
        'name' => $name
      ];
    }
    return $records;
  }

}