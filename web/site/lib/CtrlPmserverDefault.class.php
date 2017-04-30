<?php

class CtrlPmserverDefault extends CtrlDefault {
  use CrudItemsCtrl;

  protected function getParamActionN() {
    return 0;
  }

  protected function _items() {
    return new PmProjectItems;
  }

  protected function id() {
    return $this->req->param(1);
  }

  protected function getGrid() {
    return new GridData(new PmProjectFields, $this->items(), ['id' => 'name']);
  }

  function action_ajax_default() {
    $this->ajaxOutput = '<h1>Ngn PM Server</h1>';
  }

  function action_json_auth() {
    $form = new PmAuthForm;
    $r = $this->jsonFormActionUpdate($form);
    if ($r === true) $this->json['token'] = $form->token;
    return $r;
  }

  function action_json_logout() {
    unset($_SESSION['token']);
    $this->json['success'] = true;
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