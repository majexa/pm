<?php

class PmProjectFields extends Fields
{

  function __construct() {
    parent::__construct([
      [
        'title' => 'Домен',
        'name' => 'domain',
        'required' => true
      ],
      [
        'title' => 'Имя',
        'name' => 'name',
        'required' => true
      ],
      [
        'title' => 'Тип',
        'name' => 'kind',
        'type' => 'select',
        'options' => [
          'project',
          'php'
        ]
      ],
      [
        'title' => 'Тип проекта',
        'name' => 'type',
        'system' => true
      ]
    ]);
  }

}