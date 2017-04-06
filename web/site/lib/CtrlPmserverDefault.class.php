<?php

class CtrlPmserverDefault extends CtrlDefault {
  use CrudAbstractCtrl;

  protected function items() {
    return new PmProjectItems;
  }

  protected function id() {
    $this->req['id'];
  }

  protected function getGrid() {
    return new GridData(new Fields([
      [
        'title' => 'Домен',
        'name' => 'domain'
      ],
      [
        'title' => 'Имя',
        'name' => 'name'
      ]
    ]), $this->items(), [
      'id' => 'name'
    ]);
  }

}