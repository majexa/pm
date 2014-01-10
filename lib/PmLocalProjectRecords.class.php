<?php

class PmLocalProjectRecords {

  protected $config;

  function __construct() {
    $this->config = new PmLocalServerConfig;
  }

  function getRecords() {
    if (!file_exists($this->config->r['projectRecordsFile'])) return [];
    return require $this->config->r['projectRecordsFile'];
  }

  function getRecord($name) {
    return Arr::getValueByKey($this->getRecords(), 'name', $name);
  }

  function saveRecord(array $v) {
    $v = Arr::filterByKeys($v, ['name', 'domain', 'type']);
    Arr::checkEmpty($v, 'domain');
    $records = $this->getRecords();
    if (($k = Arr::getKeyByValue($records, 'domain', $v['domain'])) !== false) $records[$k] = $v;
    else $records[] = $v;
    $this->saveRecords($records);
  }

  function delete($name) {
    $this->saveRecords(Arr::dropBySubKeys($this->getRecords(), 'name', $name));
  }

  function updateDomain($oldDomain, $newDomain) {
    $this->updateProperty('domain', $oldDomain, $newDomain);
  }

  function updateName($oldName, $newName) {
    $this->updateProperty('name', $oldName, $newName);
  }

  protected function updateProperty($prop, $old, $new) {
    $records = $this->getRecords();
    if (($k = Arr::getKeyByValue($records, $prop, $old)) === false) throw new Exception("Record $prop='$old' does not exists");
    $records[$k][$prop] = $new;
    $this->saveRecords($records);
  }

  protected function saveRecords(array $records) {
    FileVar::updateVar($this->config->r['projectRecordsFile'], $records);
  }

  /**
   * Добавляет в массив проектов новый проект по исходной записе в формате:
   *
   * @param   array   Массив с записями проектов
   * @param   string  Имя проекта/домен
   * @param   array   Пример исходной записи:
   *
   *                  array(
   *                    'serverHost' => 'localhost',
   *                    'ftpUser' => 'user',
   *                    'ftpPass' => 'pass',
   *                    'ftpRoot' => 'ngn-env',
   *                    'ftpWebroot' => 'ngn-env/$projectsFolder/'.$name.'/webroot',        путь до www корня сайта относительно корневого FTP каталога
   *                    'dbName' => Misc::domain2dbname($name),
   *                    'dbUser' => 'root',
   *                    'dbPass' => 'root',
   *                    'dbHost' => 'localhost',
   *                    'sshUser' => 'masted',
   *                    'sshPass' => '123',
   *                    'ngnEnvPath' => '/home/masted/ngn-env',                        путь до корня NGN окружения
   *                    'webroot' => '$ngnEnvPath/$projectsFolder/'.$name.'/webroot',   путь до www корня сайта относительно корня NGN окружения
   *                    'ngnPath',
   *                    'vendorsPath'
   *                  )
   *
   *                  или
   *
   *                  array(
   *                    'tpl' => 'asdqwdqwd',
   *                  )
   *
   *                  или
   *
   *                  array(
   *                    'server' => 'local',
   *                  )
   *
  protected function addMixedRecord(array &$projects, $domain, array $mixedRecord) {
  try {
  if (isset($mixedRecord['tpl']) or isset($mixedRecord['server'])) {
  if (isset($mixedRecord['tpl'])) {
  $this->addProjectRecordByTpl($projects, $domain, $mixedRecord['tpl']);
  } else {
  $this->addProjectRecordByServer($projects, $domain, $mixedRecord['server']);
  }
  // Добавляем дополнительные параметры для продакш проекта. Пока только "aliases"
  if (isset($mixedRecord['aliases']))
  $projects[$domain]['aliases'] = $mixedRecord['aliases'];
  } else {
  $this->addProjectRecord($projects, $domain, $mixedRecord);
  }
  } catch (Exception $e) {
  throw new Exception($e->getMessage().'. record "'.$domain.'": '.getPrr($mixedRecord));
  }
  }

  protected function addProjectRecordByTpl(&$projects, $domain, $tpl) {
  File::checkExists($this->oLSC->r['tplsPath'].'/'.$tpl.'.php');
  $records = require $this->oLSC->r['tplsPath'].'/'.$tpl.'.php';
  foreach ($records as $domain => $record)
  $this->addMixedRecord($projects, $domain, $record);
  }

  // ------
  protected function addProjectRecordByServer(&$projects, $domain, $serverName) {
  $record = $this->getServerRecord($server, $domain);
  $record['server'] = $server;
  $this->addProjectRecord($projects, $domain, $record);
  }

  function getServerRecord($serverName) {
  File::checkExists($this->oLSC->r['remoteServersPath'].'/'.$remoteServerName.'.php');
  $record = require $this->oLSC->r['remoteServersPath'].'/'.$remoteServerName.'.php';
  return $record;
  }
   */

}