<?php

class PmRecords extends ArrayAccesseble {

  function __construct() {
    $this->addRecords('system');
    $this->addRecords('project');
    $this->addRecords('php');
  }

  protected function addRecords($kind) {
    $records = PmRecord::factory(['name' => 'dummy', 'kind' => $kind])->getRecords();
    if ($records == 1) die2($kind);
    foreach ($records as &$r) {
      $r['kind'] = $kind;
      $r = PmRecord::factory($r);
    }
    $this->r = array_merge($this->r, $records);
  }

  function offsetGet($offset) {
    return $this->getRecord($offset);
  }

  function getRecord($name) {
    return Arr::getValueByKey($this->r, 'name', $name);
  }

  function remove() {
    foreach ($this->r as $record) {
      /* @var $record PmRecord */
      Dir::clear($record->getVhostFolder());
    }
  }

  function save() {
    foreach ($this->r as $record) {
      /* @var $record PmRecord */
      if ($record->isWritable()) {
        $record->saveRecord();
      }
      $record->saveVhost();
    }
  }

//  function existsInAnotherKind($domain, $kind) {
//    foreach ($this->)
//  }

}