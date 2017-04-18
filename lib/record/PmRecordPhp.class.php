<?php

class PmRecordPhp extends PmRecordWritable {

  protected function getVhostRecord() {
    Arr::checkEmpty($this->r, 'webroot');
    return $this->renderVhostRecord($this->config['abstractVhostTttt'], $this->r);
  }

  protected function _getVhostFolder() {
    return $this->config['configPath'].'/nginx/php';
  }

  protected function getRecordsFile() {
    return $this->config['configPath'].'/php.php';
  }

}