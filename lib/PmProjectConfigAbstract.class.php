<?php

abstract class PmProjectConfigAbstract extends ArrayAccesseble {

  /**
   * @var PmServerConfigAbstract
   */
  public $config;

  public $name;

  function __construct($name) {
    Misc::checkEmpty($name);
    $this->config = $this->getServerConfig();
    $this->name = $name;
    $this->r = $this->config->r;
    $this->r['projectKey'] = $this->name;
    $this->r['dbName'] = $this->getDbName($this->name);
    $this->r['webroot'] = str_replace('{domain}', $this->name, $this->r['webroot']);
    $this->r['realWebroot'] = realpath($this->r['webroot']);
    $this->renderConfig('webroot');
    $this->r['ftpWebroot'] = str_replace('{domain}', $this->name, $this->r['ftpWebroot']);
  }

  protected function renderConfig($name) {
    foreach ($this->r as $k => $v) {
      if (!is_array($v)) {
        $this->r[$k] = St::tttt($v, [$name  => $this->r[$name]]);
      }
    }
  }

  protected function renderConfigAll() {
    foreach ($this->r as $name => $v) if (!is_array($v)) $this->renderConfig($name);
  }

  function getDbName() {
    return $this->config['name'];
  }

  abstract function getServerConfig();

}