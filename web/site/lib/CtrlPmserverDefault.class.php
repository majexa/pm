<?php

class CtrlPmserverDefault extends CtrlDefault {
  use CrudAbstractCtrl;

  protected function getParamActionN() {
    return 0;
  }

  protected function items() {
    return new PmProjectItems;
  }

  protected function id() {
    return $this->req->param(1);
  }

  protected function _getIm() {

  }

  protected function getGrid() {
    return new GridData((new PmProjectForm)->fields, $this->items(), [
      'id' => 'name'
    ]);
  }

  function action_json_update() {
    $this->json['asd'] = 'asd';
    //return $this->jsonFormActionUpdate((new PmProjectForm));
  }

}