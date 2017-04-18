<?php

class PmRecordStatic extends PmRecordWritable {

  protected function getVhostRecord() {
    Arr::checkEmpty($this->r, 'webroot');
    $vhostTttt = '
server {
  listen 80;
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