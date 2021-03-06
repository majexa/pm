<?php

class PmLocalServerConfig extends PmServerConfigAbstract {

  function init() {
    parent::init();
    foreach ($this->getPathsToCheckExistence() as $k => $v) {
      if (!St::hasTttt($v) and !file_exists($v)) {
        throw new Exception("$k '$v' does not exists");
      }
    }
    if (empty($this->r['host'])) $this->r['host'] = '127.0.0.1';
    if (empty($this->r['dbHost'])) $this->r['dbHost'] = '127.0.0.1';
    if (empty($this->r['dbUser'])) $this->r['dbUser'] = 'root';
    $this->r['remoteServersPath'] = $this->r['configPath'].'/servers';
    $this->r['tplsPath'] = $this->r['configPath'].'/tpls';
    $this->r['projectRecordsFile'] = $this->r['configPath'].'/projects.php';
  }

  protected function getPathsToCheckExistence() {
    return Arr::filterFunc(Arr::filterFunc($this->r, function($v, $k) {
      return strstr($k, 'Path');
    }), function($v, $k) {
      return !in_array($k, [
        'backupPath', 'pmPath', 'runPath', 'scriptsPath', 'projectsPath', 'dummyProjectPath'
      ]);
    });
  }

  function getFile() {
    return dirname(dirname(__DIR__)).'/config/server.php';
  }

  protected function getName() {
    return 'local';
  }

  protected function getLocalConfig() {
    return $this;
  }

}
