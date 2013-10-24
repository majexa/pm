<?php

class PmRouter extends Router {

  protected $isDB = false;
  
  function getController() {
    return new CtrlCommonPm($this);
  }
  
}
