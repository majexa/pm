<?php

class PmRecordPhp extends PmRecordWritable {

  protected function getVhostRecord() {
    $str = St::tttt($this->config['abstractVhostTttt'], $this->r, false);
    $str = preg_replace('/^\s*\n/m', '', $str);
    return $str;
  }

  protected function _getVhostFolder() {
    return $this->config['configPath'].'/nginx/php';
  }

  protected function getRecordsFile() {
    return $this->config['configPath'].'/php.php';
  }

}