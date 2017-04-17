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
        'options' => Arr::toOptions(PmRecordsWritable::getWritableKinds())
      ],
      [
        'title' => 'Тип проекта',
        'name' => 'type',
        'system' => true
      ],
      [
        'type' => 'header',
        'name' => 'kindPhp'
      ],
      [
        'title' => 'Веб-корень',
        'name' => 'webroot'
      ],
      [
        'type' => 'header',
        'name' => 'kindProxy'
      ],
      [
        'title' => 'Порт',
        'name' => 'port',
        'type' => 'integer'
      ],
      [
        'type' => 'header',
        'name' => 'kindStatic'
      ],
    ]);
  }

}