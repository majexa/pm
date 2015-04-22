<?php

abstract class PmServerConfigAbstract extends PmConfigAbstract {

  abstract function getFile();

  abstract protected function getName();

  abstract protected function getLocalConfig();

  protected function getWebserverRecords() {
    static $r;
    if (isset($r)) return $r;
    $server = $this->r; // используется в инклюде
    $r = include $this->getLocalConfig()->r['pmPath'].'/defaultWebserverRecords/'.$this->r['webserver'].'.php';
    if (!is_array($r)) throw new Exception('It is not array "defaultWebserverRecords/'.$this->r['webserver'].'.php"');
    return $r;
  }

  protected function getConfigData() {
    return include $this->getFile();
  }

  protected function init() {
    File::checkExists($this->getFile());
    $this->r = $this->getConfigData();
    $this->r['serverName'] = $this->getName();
    if (!isset($this->r['httpPort'])) $this->r['httpPort'] = 80;
    if (!isset($this->r['os'])) $this->r['os'] = 'linux';
    if (!isset($this->r['sType'])) $this->r['sType'] = 'dev';
    if (!isset($this->r['ngnEnvPath'])) $this->r['ngnEnvPath'] = NGN_ENV_PATH;
    if (!isset($this->r['baseDomain']) and $this->r['os'] == 'linux') $this->r['baseDomain'] = gethostname();
    Arr::checkIsset($this->r, ['sType']);
    foreach ([
      'ngnPath',
      'configPath',
      'projectsPath',
      'pmPath',
      'runPath',
      'dummyProjectPath',
      'logsPath',
      'scriptsPath'
    ] as $path) {
      if (!isset($this->r[$path])) $this->r[$path] = $this->r['ngnEnvPath'].'/'.str_replace('Path', '', $path);
    }
    foreach ([
      'tempPath',
      'backupPath',
    ] as $path) {
      if (!isset($this->r[$path])) $this->r[$path] = $this->r['ngnEnvPath'].'/pm/'.str_replace('Path', '', $path);
    }
    if (empty($this->r['webserver'])) $this->r['webserver'] = 'nginx';
    $this->r += $this->getWebserverRecords();
  }

  protected function afterInit() {
    parent::afterInit();
    // добавляются, только если ещё не определены
    $this->r += [
      'webroot'                       => $this->r['projectsPath'].'/{name}',
      'adminEmail'                    => 'dummy@dummy.com',
      'webserverSystemConfigFolder'   => $this->r['configPath'].'/'.$this->r['webserver'].'/system',
      'webserverProjectsConfigFolder' => $this->r['configPath'].'/'.$this->r['webserver'].'/projects'
    ];
  }

  protected function replacePaths() {
    foreach ($this->r as $k => &$v) {
      if (strstr($k, 'Path')) {
        if (is_array($v)) foreach ($v as &$value) $value = St::tttt($value, $this->r[$k]);
        else $v = St::tttt($v, $this->r);
      }
    }
  }

}