<?php

class PmProjectsManager extends DataManagerAbstract {

  protected $protoRecord;

  function __construct(PmRecord $record, array $options = []) {
    $this->protoRecord = $record;

    parent::__construct(new PmProjectForm(), $options);
  }

  function getItem($name) {
    return $this->protoRecord;
  }

  protected function _create() {
    return $this->protoRecord['name'];
  }

  protected function _update() {
    $record = clone $this->protoRecord;
    $record->r = array_merge($record->r, $this->data);
    $record->save();
  }

  protected function _delete() {
    throw new Exception('not realized');
  }

  function _updateField($id, $fieldName, $value) {
    throw new Exception('not realized');
  }

}