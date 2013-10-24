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
    $r = include $this->getLocalConfig()->r['ngnEnvPath'].'/defaultWebserverRecords/'.$this->r['webserver'].'.php';
    if (!is_array($r)) throw new Exception('It is not array "defaultWebserverRecords/'.$this->r['webserver'].'.php"');
    return $r;
  }

  function __construct() {
    File::checkExists($this->getFile());
    $this->r = include $this->getFile();
    $this->r['serverName'] = $this->getName();
    Arr::checkIsset($this->r, [
      //'host',
      'sType',
      'os',
      //'ftpUser',
      //'ftpPass',
      //'ftpRoot',
      //'ftpWebroot',
      //'dbUser',
      //'dbPass',
      //'dbHost',
      'ngnEnvPath',
      //'webserver'
    ]);
    foreach ([
      'ngnPath',
      'tempPath',
      'configPath',
      'logsPath',
      'projectsPath',
      'backupPath',
      'pmPath',
      'runPath',
      'dummyProjectPath',
      'scriptsPath'
    ] as $path) {
      if (!isset($this->r[$path])) $this->r[$path] = $this->r['ngnEnvPath'].'/'.str_replace('Path', '', $path);
    }
    Arr::checkEmpty($this->r, 'webserver');
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