<?php

class PmRecordsWritable extends PmRecordsExisting {

  static function getWritableKinds() {
    return ClassCore::getNames('PmRecord', 'PmRecordWritable');
  }

  function __construct() {
    parent::__construct();
    $this->r = array_filter($this->r, function(PmRecord $v) {
      return $v->isWritable();
    });
  }

  function removeRecord($name) {
    $index = Arr::getKeyByValue($this->r,'name', $name);
    if ($index === false) throw new Exception('Record "' . $name . '" does not exists');
    unset($this->r[$index]);
  }

  function store() {
    $grouped = [];
    foreach ($this->getArray() as $v) {
      $kind = $v['kind'];
      unset($v['kind']);
      $grouped[$kind][] = $v;
    }
    foreach (self::getWritableKinds() as $kind) {
      /* @var $writableRecordModel PmRecordWritable */
      $writableRecordModel = PmRecord::model($kind);
      $writableRecordModel->saveRecords(isset($grouped[$kind]) ? $grouped[$kind] : []);
    }
  }

  function delete($name) {
    $this->removeRecord($name);
    $this->store();
  }

}