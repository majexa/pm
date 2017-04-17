<?php

class PmProjectEditForm extends PmProjectForm {

  function __construct(PmRecord $record) {
    $fields = new PmProjectFields;
    $fields->fields['kind']['disabled'] = true;
    parent::__construct($fields);
    $this->setElementsData($record->r);
  }

  function _update(array $data) {
    PmRecord::factory($data)->save();
  }

}
