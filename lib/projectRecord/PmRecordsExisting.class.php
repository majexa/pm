<?php

class PmRecordsExisting extends PmRecords {

  function __construct() {
    $this->addRecords('system');
    $this->addRecords('project');
    $this->addRecords('php');
  }

  protected function addRecords($kind) {
    $records = PmRecord::model($kind)->getRecords();
    foreach ($records as &$r) {
      $r['kind'] = $kind;
      $r = PmRecord::factory($r);
    }
    $this->r = array_merge($this->r, $records);
  }


}