<?php

class PmRecordsExisting extends PmRecords {

  protected $existingKinds = [];

  function __construct() {
    foreach (ClassCore::getNames('PmRecord', 'PmRecord') as $kind) {
      if ($this->addRecords($kind)) {
        $this->existingKinds[] = $kind;
      }
    }
  }

  protected function addRecords($kind) {
    $records = PmRecord::model($kind)->getRecords();
    foreach ($records as &$r) {
      $r['kind'] = $kind;
      $r = PmRecord::factory($r);
    }
    $this->r = array_merge($this->r, $records);
    return $records;
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
    $this->saveAllVhost();
  }

  protected function saveAllVhost() {
    $conf = '';
    $root = O::get('PmLocalServerConfig')['configPath'].'/nginx';
    foreach ($this->existingKinds as $kind) {
      $conf .= "include $root/$kind/*;\n";
    }
    file_put_contents("$root/all.conf", $conf);
  }

}