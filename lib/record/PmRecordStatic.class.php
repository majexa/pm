<?php

class PmRecordStatic extends PmRecordWritable {

  function __construct(array $record) {
    if (empty($record['port'])) $record['port'] = 80;
    parent::__construct($record);
  }

  protected function getVhostRecord() {
    Arr::checkEmpty($this->r, 'webroot');
    $vhostTttt = '
server {
  listen {port};
  server_name {domain};
  location / {
    root {webroot};
    try_files $uri $uri/ =404;
  }
  {end}
}
';
    return $this->renderVhostRecord($vhostTttt, $this->r);
  }

  protected function _getVhostFolder() {
    return $this->config['configPath'].'/nginx/static';
  }

  protected function getRecordsFile() {
    return $this->config['configPath'].'/static.php';
  }

}