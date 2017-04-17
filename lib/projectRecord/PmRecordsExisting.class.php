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

  function clearVhosts() {
    foreach (['system', 'project', 'php'] as $kind) {
      $recordModel = PmRecord::model($kind);
      Dir::clear($recordModel->getVhostFolder());
    }
  }

  function regenVhosts() {
    $this->clearVhosts();
    $this->saveVhosts();
  }

}