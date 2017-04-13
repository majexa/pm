<?php

class PmProjectCreateForm extends Form {

  function __construct() {
    parent::__construct(new PmProjectFields);
  }

  function _update(array $data) {
    PmLocalProjectCore::create($data);
  }

}