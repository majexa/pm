<?php

/**
 * Управление существующими проектами
 */
class PmLocalProject extends ArrayAccessebleOptions {
  use PmDatabase;

  /**
   * @var PmLocalProjectConfig
   */
  public $config;

  static $requiredOptions = ['name'];

  function init() {
    $this->config = new PmLocalProjectConfig($this->options['name']);
  }

  protected function &getArrayRef() {
    return $this->config->r;
  }

  function dbExists() {
    return Db::dbExists($this->config['name'], $this->config);
  }

  function updateDomain($newDomain) {
    $this->_updateDomain($newDomain);
    return (new PmLocalServer)->updateHosts();
  }

  function _updateDomain($newDomain) {
    (new PmLocalProjectRecords)->updateDomain($this->config['domain'], $newDomain);
    $this->updateConstant('more', 'SITE_DOMAIN', $newDomain, false);
    PmDnsManager::get()->rename($this->config['domain'], $newDomain);
  }

  /**
   * Выводит крон-строку, динамически сгенерированую для этого проекта
   */
  function a_cron() {
    return Errors::checkText( //
      Cli::shell("php {$this->config['webroot']}/cmd.php cron quietly", false), //
      'project "'.$this->config['name'].'"' //
    );
  }

  /**
   * Инсталлирует всех демонов, необходимых для проекта
   */
  function a_daemons() {
    $this->cmd('daemon/install', true);
  }

  /**
   * Перезагружает демонов
   */
  function a_restart() {
    foreach (['queue', 'wss'] as $name) {
      sys("[ ! -f /etc/init.d/{$this->config['name']}-$name ] || sudo /etc/init.d/{$this->config['name']}-$name restart", true);
    }
  }

  function updateName($newName) {
    $this->_updateName($newName);
    return (new PmLocalServer)->updateHosts();
  }

  function _updateName($newName) {
    (new PmLocalProjectRecords)->updateName($this->config['name'], $newName);
    $this->updateConstant('core', 'PROJECT_KEY', $newName, false);
    rename($this->config['webroot'], dirname($this->config['webroot']).'/'.$newName);
  }

  /**
   * Копирует проект
   *
   * @options copyName, copyDomain
   */
  function a_copy() {
    $this->copy($this->options['copyName'], $this->options['copyDomain']);
  }

  /**
   * Очищает все кэши проекта
   *
   */
  function a_cc() {
    $this->cmd('cc');
  }

  function cmd($cmd, $params = '') {
    return Cli::shell("php {$this->config['webroot']}/cmd.php $cmd".($params ? ' '.$params : ''));
  }

  /**
   * Выполняет комманду на проекте
   *
   * @options command, params
   */
  function a_cmd() {
    print $this->cmd('"'.$this->options['command'].'"', empty($this->options['params']) ? '' : $this->options['params']);
  }

  /**
   * Применяет к проекту актуальные патчи
   */
  function a_patch() {
    if (!$this->dbExists()) return;
    $this->cmd("'(new FilePatcher)->patch()'");
    $this->cmd("'(new DbPatcher)->patch()'");
  }

  /**
   * Устанавливает идентификаторам последних применённых патчей самое последее актуальное значение
   */
  function a_updatePatchIds() {
    $this->cmd("'(new FilePatcher)->updateProjectFromLib()'");
    if ($this->dbExists()) $this->cmd("'(new DbPatcher)->updateProjectFromLib()'");
  }

  function importDummyDb() {
    $this->createDb($this->config['dbName']);
    $file = $this->config['ngnPath'].'/dummy.sql';
    $c = file_get_contents($file);
    if (!preg_match('/-- version: (\d+)/m', $c, $m)) throw new Exception("Version not found in $file");
    if ((new DbPatcher)->getLastPatchLibIds()['ngn'] > $m[1]) throw new Exception('Current dummy.sql version is less then ngn version. Please fix that');
    $this->_importDummyDb($this->config['dbName']);
  }

  function getVar($name) {
    $file = "{$this->config['webroot']}/site/config/vars/$name.php";
    return file_exists($file) ? include $file : false;
  }

  /**
   * @options adminPass, noPages
   */
  function runInstallers() {
    $siteRunner = "php {$this->config['runPath']}/site.php";
    // Выполняет общий инсталятор
    sys("$siteRunner {$this->config['name']} NGN_ENV_PATH/pm/installers/common ".Cli::arrayToStrParams(Arr::filterByKeys($this->options, 'adminPass')), true);
  }

  function localDownloadFs() {
    Dir::copy($this->config['webroot'], PmManager::$tempPath.'/webroot');
    return PmManager::$tempPath.'/webroot';
  }

  function localDownloadDb() {
    O::get('Db', $this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName'])->export(PmManager::$tempPath.'/db.sql');
    return PmManager::$tempPath.'/db.sql';
  }

  function copy($newName, $newDomain) {
    $this->_copy($newName, $newDomain);
    PmWebserver::get()->restart();
  }

  protected function _copy($newName, $newDomain) {
    $newRecord = (new PmLocalProjectRecords)->getRecord($this->config['name']);
    $newRecord['name'] = $newName;
    $newRecord['domain'] = $newDomain;
    PmLocalProjectCore::createRecordAndVhost($newRecord);
    $newConfig = new PmLocalProjectConfig($newName);
    Dir::copy($this->config['webroot'], $newConfig['webroot']);
    PmLocalProjectFs::updateConstant($newConfig['webroot'], 'more', 'SITE_DOMAIN', $newDomain);
    PmLocalProjectFs::updateConstant($newConfig['webroot'], 'core', 'PROJECT_KEY', $newName);
    if (empty($newConfig['noDb'])) {
      Mysql::copyDb($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config->getDbName(), $newConfig->getDbName());
      //PmLocalProjectFs::updateConstant($newConfig['webroot'], 'database', 'DB_NAME', $newConfig->getDbName());
    }
  }

  function updateConstant($k, $name, $v, $strict = true) {
    PmLocalProjectFs::updateConstant($this->config['webroot'], $k, $name, $v, $strict);
  }

  function replaceConstant($k, $name, $v, $strict = true) {
    PmLocalProjectFs::replaceConstant($this->config['webroot'], $k, $name, $v, $strict);
  }

  /**
   * Апдейтит константу проекта
   *
   * @options configKey, configName, configValue
   */
  function a_replaceConstant() {
    $this->replaceConstant($this->options['configKey'], $this->options['configName'], $this->options['configValue']);
  }

  /**
   * Апдейтит значение элемента массива конфиг-переменной
   *
   * @options configKey, configSubKey, configValue
   */
  function a_updateSubVar() {
    Config::updateSubVar($this->config['webroot'].'/site/config/vars/'.$this->options['configKey'].'.php', $this->options['configSubKey'], $this->options['configValue']);
  }

  /**
   * Апдейтит значение элемента массива конфиг-переменной, если она существует
   *
   * @options configKey, configSubKey, configValue
   */
  function a_updateSubVarIfExists() {
    $file = $this->config['webroot'].'/site/config/vars/'.$this->options['configKey'].'.php';
    if (!file_exists($file)) {
      output($this->options['name'].' skipped');
      return;
    }
    output($this->options['name'].' updated');
    Config::updateSubVar($file, $this->options['configSubKey'], $this->options['configValue']);
  }

  /**
   * Приводит некоторые константы проекта в актуальное состояние
   */
  function a_updateConfig() {
    $this->updateConstant('more', 'SITE_DOMAIN', $this->config['domain']);
    $this->updateConstant('core', 'IS_DEBUG', $this->config['sType'] == 'prod' ? false : true);
    $this->updateConstant('site', 'ALLOW_SEND', $this->config['sType'] == 'prod' ? true : false);
  }

  protected function supports($name) {
    return (bool)Cli::shell("php ".NGN_ENV_PATH."/run/run.php site {$this->config['name']} \"print (bool)Config::getVar('$name', true)\"", false);
  }

  /**
   * Приводит index.php проекта в актуальное состояние
   */
  function a_updateIndex() {
    foreach (['index', 'cmd', 'queue', 'wss'] as $name) File::delete("{$this->config['webroot']}/$name.php");
    $this->copyIndexFile('index', true);
    $this->copyIndexFile('cmd', true);
    foreach (['queue', 'wss'] as $name) if ($this->supports($name)) $this->copyIndexFile($name, true);
    $c = file_get_contents($this->config['webroot'].'/index.php');
    file_put_contents($this->config['webroot'].'/index.php', $c);
    Config::updateConstant($this->config['webroot'].'/index.php', 'NGN_PATH', $this->config['ngnPath']);
    Config::updateConstant($this->config['webroot'].'/cmd.php', 'NGN_PATH', $this->config['ngnPath']);
    if ($this->config['webserver'] == 'apache') {
      copy($this->config['dummyProjectPath'].'/.htaccess', $this->config['webroot'].'/.htaccess');
    }
  }

  protected function copyIndexFile($name, $force = false) {
    if (!file_exists("{$this->config['webroot']}/$name.php")) {
      if ($force) {
        output("create '$name' of '".basename($this->config['webroot'])."' project");
        copy("{$this->config['dummyProjectPath']}/$name.php", "{$this->config['webroot']}/$name.php");
        return;
      }
      return;
    }
    if (!`diff {$this->config['dummyProjectPath']}/$name.php {$this->config['webroot']}/$name.php`) return;
    if (filemtime("{$this->config['dummyProjectPath']}/$name.php") == filemtime("{$this->config['webroot']}/$name.php")) return;
    output("update '$name' of '".basename($this->config['webroot'])."' project");
    copy("{$this->config['dummyProjectPath']}/$name.php", "{$this->config['webroot']}/$name.php");
  }

  function importFsFromLocal($tempWebroot) {
    PmLocalProjectFs::updateDbConfig($tempWebroot, $this->config->r);
    PmLocalProjectFs::updateConstant($tempWebroot, 'site', 'SITE_DOMAIN', $this->config['name']);
    PmLocalProjectFs::updateConstant($tempWebroot, 'core', 'IS_DEBUG', true);
    PmLocalProjectFs::updateConstant($tempWebroot, 'core', 'DO_NOT_LOG', false);
    //$this->updateIndex($tempWebroot);
    Dir::copy($tempWebroot, $this->config['webroot']);
  }

  /**
   * @return Db
   */
  protected function getDb() {
    return O::get('Db', $this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName']);
  }

  function importDbFromLocal($dumpFile) {
    Db::deleteDb($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName']);
    Db::createDb($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName']);
    $this->getDb()->importFile($dumpFile);
  }

  function dbParams() {
    return "-u {$this->config['dbUser']} -p{$this->config['dbPass']}";
  }

  function dbQuery($q) {
    sys("mysql ".$this->dbParams()." -e '$q'");
  }

  function a_addBasicHost() {
    $records = new PmLocalProjectRecords();
    $record = $records->getRecord($this->options['name']);
    $basicProjectHost = $record['name'].'.'.$this->config['baseDomain'];
    if (!isset($record['aliases'])) $record['aliases'] = [];
    if (!in_array($basicProjectHost, $record['aliases'])) $record['aliases'][] = $basicProjectHost;
    if (!in_array('*.'.$basicProjectHost, $record['aliases'])) $record['aliases'][] = '*.'.$basicProjectHost;
    $records->saveRecord($record);
  }

  function a_removeBasicHost() {
    $records = new PmLocalProjectRecords();
    $record = $records->getRecord($this->options['name']);
    $basicProjectHost = $record['name'].'.'.$this->config['baseDomain'];
    Arr::remove($record['aliases'], $basicProjectHost);
    Arr::remove($record['aliases'], '*.'.$basicProjectHost);
    $record['aliases'] = array_values($record['aliases']);
    $record = Arr::filterEmpties($record);
    $records->saveRecord($record);
  }

  /**
   * Апдейтит projects.php, SITE_DOMAIN, DNS и перезагружает веб-сервер
   *
   * @options newDomain
   */
  function __updateDomain() {
    $this->updateDomain($this->options['newDomain'])->restart();
  }

  /**
   * Апдейтит projects.php, PROJECT_KEY, переименовывает папку проекта и перезагружает веб-сервер
   *
   * @options newName
   */
  function __updateName() {
    $this->updateName($this->options['newName']);
  }

  /**
   * Удаляет проект
   */
  function a_delete() {
    Dir::remove($this->config['webroot']);
    Db::deleteDb($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName']);
    (new PmLocalProjectRecords)->delete($this->config['name']);
    PmDnsManager::get()->delete($this->config['name']);
    PmWebserver::get()->delete($this->config['name'])->restart();
  }

}