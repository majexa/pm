<?php

class PmRecordProxy extends PmRecordWritable {

  protected function getVhostRecord() {
    $vhostTttt = '
server {
    listen 80;

    server_name {domain};

    location / {
        proxy_pass http://localhost:{port};
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection \'upgrade\';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
';
    return $this->renderVhostRecord($vhostTttt, $this->r);
  }

  protected function _getVhostFolder() {
    return $this->config['configPath'].'/nginx/proxy';
  }

  protected function getRecordsFile() {
    return $this->config['configPath'].'/proxy.php';
  }

}