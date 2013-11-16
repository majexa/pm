<?php

abstract class PmServerConfigAbstract extends ArrayAccesseble {

  public $record;

  abstract function getFile();

  abstract protected function getName();

  abstract protected function getLocalConfig();

  protected function getConfig() {
    static $r;
    if (isset($r)) return $r;
    $server = $this->r; // используется в инклюде
    $r = include $this->getLocalConfig()->r['pmPath'].'/defaultWebserverRecords/'.$this->r['webserver'].'.php';
    die2($r);

    if (!is_array($r)) throw new Exception('It is not array "defaultWebserverRecords/'.$this->r['webserver'].'.php"');
    return $r;
  }

  function __construct() {
    File::checkExists($this->getFile());
    $this->r = include $this->getFile();
    $this->r['serverName'] = $this->getName();
    if (!isset($this->r['os'])) $this->r['os'] = 'linux';
    if (!isset($this->r['sType'])) $this->r['sType'] = 'dev';
    if (!isset($this->r['ngnEnvPath'])) $this->r['ngnEnvPath'] = NGN_ENV_PATH;
    if (!isset($this->r['baseDomain']) and $this->r['os'] == 'linux') $this->r['baseDomain'] = gethostname();
    Arr::checkIsset($this->r, [
      //'host',
      'sType',
      //'os',
      //'ftpUser',
      //'ftpPass',
      //'ftpRoot',
      //'ftpWebroot',
      //'dbUser',
      //'dbPass',
      //'dbHost',
      //'ngnEnvPath',
      //'webserver'
    ]);
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
    // добавляем записи для этого вебсервера по умолчанию
    $this->r += $this->getConfig();
    foreach ($this->r as $k => $v) if (strstr($k, 'Path')) $this->r[$k] = St::tttt($v, $this->r);
    $ifNotSetValues = [
      'webroot'                       => $this->r['projectsPath'].'/{domain}',
      'adminEmail'                    => 'asd@asd.sd',
      'webserverConfigFolder'         => $this->r['configPath'].'/'.$this->r['webserver'],
      'webserverProjectsConfigFolder' => $this->r['configPath'].'/'.$this->r['webserver'].'Projects'
    ];
    $this->r += $ifNotSetValues;
  }

}