<?php

class PmLocalProjectConfig extends PmProjectConfigAbstract {

  function getServerConfig() {
    return (new PmLocalServerConfig());
  }

  function getConstant($type, $name) {
    return Config::getConstant("{$this->r['webroot']}/site/config/constants/$type.php", $name, true);
  }

  function __construct($name) {
    if (($rr = (new PmLocalProjectRecords())->getRecord($name)) === false) throw new Exception("Project '$name' does not exists");
    parent::__construct($name);
    $this->r = array_merge($this->r, $rr);
    if (isset($this->r['type'])) $this->r = array_merge(PmCore::config('types')[$this->r['type']], $this->r);
    $this->r['dbName'] = $this->r['name'];
    $this->renderConfigAll();
  }

  function debug() {
    $r = [];
    foreach ($this->r as $k => $v) {
      if (is_array($v)) $r[$k] = $v;
      if (!(bool)strstr(strtolower($k), 'vhost')) $r[$k] = $v;
    }
    return $r;
  }

}
