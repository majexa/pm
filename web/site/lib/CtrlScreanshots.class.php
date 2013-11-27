<?php

class CtrlScreanshots extends CtrlCommon {

  function action_default() {
    $this->d['items'] = Dir::files(WEBROOT_PATH.'/m/captures');
    $this->d['mainTpl'] = 'screanshots';
  }

}