<?php

class PmProjectEditForm extends Form {

  function __construct(PmRecord $record) {
    parent::__construct(new PmProjectFields);
    $this->setElementsData($record->r);
  }

  function _update(array $data) {
    PmRecord::factory($data)->save();
  }

}
