<?php

class PmProjectForm extends Form {

  function __construct() {
    parent::__construct(new Fields([
      [
        'title' => 'Домен',
        'name' => 'domain',
        'required' => true
      ],
      [
        'title' => 'Имя',
        'name' => 'name',
        'required' => true
      ]
    ]));
  }

}
