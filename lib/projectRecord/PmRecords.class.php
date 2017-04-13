<?php

class PmRecords extends ArrayAccesseble {

  function __construct() {
    $this->addRecords('system');
    $this->addRecords('project');
    $this->addRecords('php');
  }

  protected function addRecords($kind) {
    $records = PmRecord::factory(['name' => 'dummy', 'kind' => $kind])->getRecords();
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

  function delete($name) {
    $index = Arr::getKeyByValue($this->r,'name', $name);
    $this->r[$index]->deleteVhost();
    $newRecords = $this->r;
    unset($newRecords[$index]);
    $this->r[$index]->saveRecords($newRecords);
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

  function regen() {
    $this->remove();
    $this->save();
  }

//  function existsInAnotherKind($domain, $kind) {
//    foreach ($this->)
//  }

}