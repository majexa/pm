<?php

class PmProjectForm extends Form {

  protected function init() {
    $this->addVisibilityCondition('kindPhp', 'kind', 'v === "php" || v === "static"');
    $this->addVisibilityCondition('kindProxy', 'kind', 'v === "proxy"');
  }

}