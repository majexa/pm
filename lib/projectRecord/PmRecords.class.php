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

  function saveVhosts() {
    Arr::get($this->r, 'name');
    foreach ($this->r as $record) {
      /* @var $record PmRecord */
      $record->saveVhost();
    }
  }

  function clearVhosts() {
  }

  function regenVhosts() {
    $this->clearVhosts();
    $this->saveVhosts();
  }

}