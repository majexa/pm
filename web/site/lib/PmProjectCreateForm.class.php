<?php

class PmProjectCreateForm extends PmProjectForm {

  function __construct() {
    parent::__construct(new PmProjectFields);
  }

  function _update(array $data) {
    PmProjectCore::create($data);
  }

}