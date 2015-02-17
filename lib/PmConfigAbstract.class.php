<?php

abstract class PmConfigAbstract extends ArrayAccesseble {

  function __construct() {
    $this->beforeInit();
    $this->init();
    $this->afterInit();
  }

  protected function beforeInit() {
  }

  abstract protected function init();

  protected function afterInit() {
    $this->renderConfigAll();
  }

  protected function renderConfig($name) {
    foreach ($this->r as &$v) {
      if (is_array($v)) {
        foreach ($v as &$value) $value = St::tttt($value, [$name => $this->r[$name]]);
      } else {
        if (is_array($this->r[$name])) continue;
        $v = St::tttt($v, [$name => $this->r[$name]]);
      }
    }
  }

  protected function renderConfigAll() {
    foreach ($this->r as $name => $v) $this->renderConfig($name);
  }

}