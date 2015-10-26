<?php

class PmLocalProjectConfig extends PmProjectConfigAbstract {

  function serverConfig() {
    return (new PmLocalServerConfig());
  }

  function getConstant($type, $name) {
    return Config::getConstant("{$this->r['webroot']}/site/config/constants/$type.php", $name, true);
  }

  public $record;

  protected function beforeInit() {
    if (($this->record = (new PmLocalProjectRecords)->getRecord($this->name)) === false) {
      throw new Exception("Project '$this->name' does not exists");
    }
  }

  protected function init() {
    parent::init();
    $this->r = $this->serverConfig()->r;
    $this->r = array_merge($this->r, $this->record);
    $this->r = array_merge($this->r, $this->typeData());
    $this->r['dbName'] = $this->r['name'];
  }

  function isNgnProject() {
    return !file_exists($this->r['webroot'].'/.nonNgn');
  }

  protected $multipleParams = ['vhostAliases', 'afterCmdTttt'];

  protected function typeData() {
    if (empty($this->r['type'])) return [];
    $types = PmCore::types();
    if (!isset($types[$this->r['type']])) throw new Exception("Type '{$this->r['type']}' does not exists");
    $type = $types[$this->r['type']];
    foreach ($this->multipleParams as $p) if (isset($type[$p])) $type[$p] = (array)$type[$p]; // normalize multiples to arrays
    if (isset($type['extends'])) {
      $extendingType = $types[$type['extends']];
      foreach ($this->multipleParams as $p) {
        if (isset($extendingType[$p])) {
          $extendingType[$p] = (array)$extendingType[$p];
          if (!isset($type[$p])) $type[$p] = [];
          $type[$p] = array_merge($extendingType[$p], $type[$p]);
        }
      }
      foreach (Arr::filterByExceptKeys($extendingType, $this->multipleParams) as $k => $v) {
        $type[$k] = $v;
      }
    }
    return $type;
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
