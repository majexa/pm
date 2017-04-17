<?php

class PmAuthForm extends Form {

  protected function defineOptions() {
    return array_merge(parent::defineOptions(), [
      'title' => 'Авторизация'
    ]);
  }

  function __construct() {
    parent::__construct([
      [
        'title' => Locale::get('password', 'users'),
        'name' => 'password',
        'type' => 'password'
      ]
    ]);
  }

  public $token;

  function _update(array $data) {
    $this->token = base64_encode('asd');
    $_SESSION['token'] = $this->token;
  }

}