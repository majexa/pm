<?php

class PmRouter extends Router {

  protected $isDB = false;
  
  function _getController() {
    return new CtrlCommonPm($this);
  }
  
}
