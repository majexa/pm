<?php

class CtrlDocs extends CtrlCommon {

  function action_default() {
    $this->d['mainTpl'] = 'clearTpl';
    $this->d['tpl'] = 'docs/'.$this->req->param(1);
    // @todo wiki or markdown format output
  }

  function action_parsed() {

    //die2(3412);
    //$this->d['mainTpl'] = 'clearTpl';
    //getNgnFileDockBlock
  }

}