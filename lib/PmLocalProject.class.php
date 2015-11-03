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
    if (!$this->config->isNgnProject()) {
      throw new Exception('"'.$this->options['name'].'" is not Ngn Project');
    }
  }

  static function paramOptions_name() {
    return Arr::get((new PmLocalProjectRecords)->getRecords(), 'name', 'name');
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
    print `run site {$this->config['name']} cron`;
//    return Errors::checkText( //
//      Cli::shell("php {$this->config['webroot']}/cmd.php cron quietly", false), //
//      'project "'.$this->config['name'].'"' //
//    );
  }

  protected function daemonNames() {
    return ['queue', 'wss'];
  }

  /**
   * Возвращает имена демонов, включенных для проекта
   *
   * @return array
   */
  protected function enabledDaemonNames() {
    return array_filter($this->daemonNames(), function($name) {
      $r = $this->getVar($name);
      return $r and empty($r['disable']);
    });
  }

  /**
   * Анинсталлирует несуществующие демоны проекта. Инсталлирует всех демонов, включенных для проекта
   *
   */
  function a_daemons() {
    $enabledDaemonNames = $this->enabledDaemonNames();
    foreach (ProjectDaemon::getInstalled($this->config['name']) as $name) {
      if (in_array($name, $enabledDaemonNames)) continue;
      (new ProjectDaemon($this->config['name'], $name))->uninstall();
    }
    foreach ($enabledDaemonNames as $name) {
      $daemon = new ProjectDaemon($this->config['name'], $name);
//      if ($daemon->exists()) {
//          $daemon->restart();
//          continue;
//      }
      if ($daemon->install()) {
        usleep(0.1 * 100000);
        $daemon->checkInstallation();
      }
    }
  }

  /**
   * Перезагружает демонов
   */
  function a_restart() {
    foreach ($this->daemonNames() as $name) {
      sys("[ ! -f /etc/init.d/{$this->config['name']}-$name ] || sudo /etc/init.d/{$this->config['name']}-$name restart");
    }
  }

  function updateName($newName) {
    $this->_updateName($newName);
    $this->a_cc();
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
   * @options command, {quietly}
   */
  function a_cmd() {
    print $this->cmd('"'.$this->options['command'].'"', empty($this->options['quietly']) ? '' : 'quietly');
  }

  /**
   * Применяет к проекту актуальные патчи
   */
  function a_patch() {
    $this->cmd('"(new FilePatcher)->patch()"');
    if ($this->dbExists()) $this->cmd('"(new DbPatcher)->patch()"');
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
    return json_decode(`run site {$this->config['name']} var $name`, JSON_FORCE_OBJECT);
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
    PmLocalProjectCore::createEmpty($newRecord);
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
    $this->copyIndexFile('cmd', true);
    if (file_exists($this->config['webroot'].'/.keepIndex')) return;
    $this->copyIndexFile('index', true);
    foreach ($this->daemonNames() as $name) if ($this->supports($name)) $this->copyIndexFile($name, true);
    Config::updateConstant($this->config['webroot'].'/index.php', 'NGN_PATH', $this->config['ngnPath']);
    Config::updateConstant($this->config['webroot'].'/cmd.php', 'NGN_PATH', $this->config['ngnPath']);
    if ($this->config['webserver'] == 'apache') {
      copy($this->config['dummyProjectPath'].'/.htaccess', $this->config['webroot'].'/.htaccess');
    }
  }

  function a_deleteLogs() {
      output('clear '.$this->config['webroot'].'/site/logs');
      Dir::clear($this->config['webroot'].'/site/logs');
  }
//
//  function a_cleanupIndex() {
//    foreach ($this->daemonNames() as $name) if (!$this->supports($name)) $this->deleteIndexFile($name);
//  }

  protected function copyIndexFile($name, $force = false) {
    if (!file_exists("{$this->config['webroot']}/$name.php")) {
      if ($force) {
        output("create '$name' of '".basename($this->config['webroot'])."' project");
        copy("{$this->config['dummyProjectPath']}/$name.php", "{$this->config['webroot']}/$name.php");
        return;
      }
      return;
    }
    if (file_get_contents("{$this->config['dummyProjectPath']}/$name.php") == file_get_contents("{$this->config['webroot']}/$name.php")) return;
    if (filemtime("{$this->config['dummyProjectPath']}/$name.php") == filemtime("{$this->config['webroot']}/$name.php")) return;
    output("update '$name' of '".basename($this->config['webroot'])."' project");
    copy("{$this->config['dummyProjectPath']}/$name.php", "{$this->config['webroot']}/$name.php");
    //Config::updateConstant("{$this->config['webroot']}/$name.php", 'NGN_PATH', NGN_PATH);
  }

  protected function deleteIndexFile($name) {
    File::delete("{$this->config['webroot']}/$name.php");
  }

  function importFs($tempWebroot) {
    //PmLocalProjectFs::updateDbConfig($tempWebroot, $this->config->r);
    //PmLocalProjectFs::updateConstant($tempWebroot, 'site', 'SITE_DOMAIN', $this->config['name']);
    //PmLocalProjectFs::updateConstant($tempWebroot, 'core', 'IS_DEBUG', true);
    //PmLocalProjectFs::updateConstant($tempWebroot, 'core', 'DO_NOT_LOG', false);
    //$this->updateIndex($tempWebroot);
    Dir::copy($tempWebroot, $this->config['webroot'], false);
  }

  function importUpload($tempWebroot) {
    Dir::copy($tempWebroot.'/'.UPLOAD_DIR, $this->config['webroot'].'/'.UPLOAD_DIR, false);
  }

  /**
   * @return Db
   */
  protected function getDb() {
    return O::get('Db', $this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $this->config['dbName']);
  }

  function importDb($dumpFile) {
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

  /**
   * Добавляет для проекта сабдомен на основе имени проекта и базового хоста сервера и перезагружает веб-сервер
   */
  function a_addBasicHost() {
    $records = new PmLocalProjectRecords();
    $record = $records->getRecord($this->options['name']);
    $basicProjectHost = $record['name'].'.'.$this->config['baseDomain'];
    if (!isset($record['aliases'])) $record['aliases'] = [];
    if (!in_array($basicProjectHost, $record['aliases'])) $record['aliases'][] = $basicProjectHost;
    if (!in_array('*.'.$basicProjectHost, $record['aliases'])) $record['aliases'][] = '*.'.$basicProjectHost;
    $records->saveRecord($record);
    PmWebserver::get()->restart();
  }

  /**
   * Удаляет хост {projectName}.{serverBaseDomain} из записи проекта и перезагружает вер-сервер
   */
  function a_removeBasicHost() {
    $records = new PmLocalProjectRecords();
    $record = $records->getRecord($this->options['name']);
    $basicProjectHost = $record['name'].'.'.$this->config['baseDomain'];
    Arr::remove($record['aliases'], $basicProjectHost);
    Arr::remove($record['aliases'], '*.'.$basicProjectHost);
    $record['aliases'] = array_values($record['aliases']);
    $record = Arr::filterEmpties($record);
    $records->saveRecord($record);
    PmWebserver::get()->restart();
  }

  /**
   * Изменяет домен в файле записей ngn-env/config/projects.php,
   * константу проекта SITE_DOMAIN;
   * обновляет DNS и перезагружает веб-сервер
   *
   * @options newDomain
   */
  function a_updateDomain() {
    $this->updateDomain($this->options['newDomain'])->restart();
  }

  /**
   * Апдейтит projects.php, PROJECT_KEY, переименовывает папку проекта и перезагружает веб-сервер
   *
   * @options newName
   */
  function a_updateName() {
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

  /**
   * Делает дамп БД и складывает его в PmManager::$tempPath/projectName.sql
   */
  function a_exportDb() {
    Dir::make(PmManager::$tempPath.'/db');
    $sqlFile = PmManager::$tempPath.'/db/'.$this->config['dbName'].'.sql';
    shell_exec('mysqldump '.Mysql::auth($this->config).' '. //
      $this->config['dbName'].' > '.$sqlFile);
    print $sqlFile;
  }

  /**
   * Импортирует БД из файла pm/temp/db/projectName.sql
   */
  function a_importDb() {
    shell_exec('mysql '.Mysql::auth($this->config).' '. //
      $this->config['dbName'].' < '. //
      PmManager::$tempPath.'/db/'.$this->options['name'].'.sql');
    print 'done.';
  }

  /**
   * Копирует файлы проекта. Для каталога project/u/dd, только изменённые за 2 последних дня
   */
  function a_exportUFolder() {
    $uFolder = $this->config['webroot'].'/'.UPLOAD_DIR;
    Dir::remove(PmManager::$tempPath.'/'.$this->config['name']);
    new UFolderLimitCopy($uFolder, PmManager::$tempPath.'/'.$this->config['name']);
    print PmManager::$tempPath.'/'.$this->config['name'];
  }

  /**
   * Выставляет нужные привилегии для папок проекта
   */
  function chmod() {
    sys('chmod -R +w '.$this->config['webroot'].'/site/data');
    sys('chmod -R +w '.$this->config['webroot'].'/site/logs');
    sys('chmod -R +w '.$this->config['webroot'].'/u');
  }

  function record() {
    print FileVar::formatVar($this->config->record);
  }

}
