<?php

class CtrlPmserverDefault extends CtrlDefault {
  use CrudItemsCtrl;

  protected function getParamActionN() {
    return 0;
  }

  protected function items() {
    return new PmProjectItems;
  }

  protected function id() {
    return $this->req->param(1);
  }

  protected function getGrid() {
    return new GridData(new PmProjectFields, $this->items(), ['id' => 'name']);
  }

  function action_json_new() {
    return $this->jsonFormActionUpdate(new PmProjectCreateForm);
  }

  function action_json_edit() {
    return $this->jsonFormActionUpdate(
      new PmProjectEditForm(
        PmRecord::factory($this->id())
      )
    );
  }

  function action_json_delete() {
    (new PmLocalProject(['name' => $this->id()]))->a_delete();
    $this->json = ['success' => true];
  }

}