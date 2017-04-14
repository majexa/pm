<?php

class PmRecords extends ArrayAccesseble {

  /**
   * @param PmRecord[] $records
   */
  function __construct(array $records) {
    foreach ($records as $record) {
      $this->r[] = $record;
    }
  }

  function offsetGet($offset) {
    return $this->getRecord($offset);
  }

  function getRecord($name) {
    return Arr::getValueByKey($this->r, 'name', $name);
  }

  function getArray() {
    $r = [];
    foreach ($this->r as $v) $r[] = $v->r;
    return $r;
  }

  function removeVhosts() {
    // TODO пробегаться только по каждой первой записи в kind'e
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

  function regen() {
    $this->removeVhosts();
    $this->save();
  }

}