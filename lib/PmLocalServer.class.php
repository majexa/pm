<?php

/**
 * Server Management: Projects Layer
 */
class PmLocalServer extends ArrayAccessebleOptions {
  use PmDatabase;

  /**
   * @var PmLocalServerConfig
   */
  protected $config;

  function init() {
    $this->config = new PmLocalServerConfig;
  }

  static function paramOptions_existingName() {
    return Arr::get((new PmLocalProjectRecords)->getRecords(), 'name', 'name');
  }

  /**
   * Show all web-server virtual hosts
   */
  function a_showHosts() {
    foreach (O::get('PmRecordsExisting') as $v) {
      print CliColors::colored("☯️ ".$v['name'], 'cyan')." — http://{$v['domain']}\n";
    }
  }

  /**
   * Updates web-server virtual hosts
   */
  function a_updateHosts() {
    $this->updateHosts()->restart();
    $this->a_showHosts();
  }

  /**
   * Updates web-server virtual hosts
   */
  function a_updateHosts2() {
    $this->updateHosts()->restart();
    $this->a_showHosts();
  }

  /**
   * Creates project folder, virtual host and setup DNS-records
   *
   * @options name, domain
   */
  function a_createEmptyProject() {
    PmProjectCore::createEmpty($this->options);
  }

  function a_createDatabaseConfig() {
    file_put_contents(
      NGN_ENV_PATH.'/config/database.php',
      "<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '{$this->config['dbPass']}');
define('DB_NAME', PROJECT_KEY);
define('DB_LOGGING', false);
");
  }

  /**
   * Creates project
   *
   * @options name, domain, @type
   */
  function a_createProject() {
    if ($this->options['domain'] == 'default') {
      $this->options['domain'] = $this->options['name'].'.'.$this->config['baseDomain'];
    }
    PmProjectCore::create($this->options);
    PmWebserver::get()->restart();
  }

  /**
   * Creates virtual host
   *
   * @options name, domain, @kind, webroot
   */
  function a_createVhost() {
    PmProjectCore::create($this->options);
    PmWebserver::get()->restart();
  }

  /**
   * Удаляет проект с указанным именем, если он уже существует и создаёт новый
   *
   * @options name, domain, @type
   */
  function a_replaceProject() {
    $this->deleteProject($this->options['name']);
    $this->a_createProject();
  }

  /**
   * Создаёт проект, если его ещё нет или если его тип отличается от текущего. Используется для создания тестового проекта
   *
   * @options {@type}
   */
  function a_createTestProject() {
    $this->options['name'] = 'test';
    $this->options['domain'] = 'test.'.$this->config['baseDomain'];
    if (($record = (new PmRecordsExisting)->getRecord($this->options['name']))) {
      if (isset($record['type']) and isset($this->options['type']) and $record['type'] != $this->options['type']) {
        $this->a_deleteProject();
        $this->a_createProject();
        output2("Project created");
        print `pm localProject replaceConstant {$this->options['name']} core IS_DEBUG true`;
      }
      else {
        output("Same project '{$this->options['name']}:{$this->options['type']}' already exists");
      }
    }
    else {
      $this->a_createProject();
      output2("Project created");
    }
  }

  /**
   * Удаляет проект, только если он существует
   *
   * @options existingName
   */
  function a_deleteProject() {
    $this->deleteProject($this->options['existingName']);
  }

  protected function deleteProject($name) {
    if (!(new PmRecordsExisting)->getRecord($name)) {
      output("Project '{$name}' does not exists");
      return;
    }
    (new PmLocalProject(['name' => $name]))->a_delete();
  }

  static function helpOpt_type() {
    return array_keys(PmProjectType::types());
  }

  static function helpOpt_kind() {
    return ['php', 'proxy'];
  }

  /**
   * Создаёт базу данных со структурой девственного проекта
   *
   * @options dbName
   */
  function a_createDummyDb() {
    $this->createDb($this->options['dbName']);
    $this->importSqlDump($this->config['ngnPath'].'/dummy.sql', $this->options['dbName']);
  }

  function updateHosts() {
    PmDnsManager::factory()->regen(O::get('PmRecordsExisting')->r);
    O::get('PmRecordsExisting')->regenVhosts();
    return PmWebserver::get();
  }

  /**
   * Создает файл дампа базы данных со структурой девственного проекта
   */
  function a_createDummyDump() {
    copy(PmCore::prepareDummyDbDump(), (new PmLocalServerConfig())->r['ngnPath'].'/dummy.sql');
  }

  protected function addToArch($what) {
    return Zip::add(PmManager::$tempPath.'/ngn-env.zip', $what);
  }

  /**
   * Выводит значение конфигурации сервера
   *
   * @options param
   */
  function a_info() {
    print $this->config[$this->options['param']]."\n";
  }

  /**
   * Устанавливает систему статистики
   */
  function a_installStat() {
    if ($this->config['stat']) {
      output('stat is already enabled');
      return;
    }
    $this->createDb('stat');
    chdir(PmManager::$tempPath);
    print `git clone https://github.com/masted/piwik`;
    print `curl -sS https://getcomposer.org/installer | php`;
    print `php composer.phar install`;
    Dir::copy(PmManager::$tempPath.'/piwik', NGN_ENV_PATH.'/stat/web');
    Dir::remove(PmManager::$tempPath.'/piwik');
    $this->a_updateHosts();
    Config::updateSubVar($this->config->getFile(), 'stat', true);
  }

  /**
   * Обновляет статистику для всех проектов
   */
  function a_updateStat() {
    print `python ~/ngn-env/stat/web/misc/log-analytics/import_logs.py --url=http://stat.{$this->config['baseDomain']}/ ~/ngn-env/logs/access.log`;
    LogWriter::str('pm', 'stat updated');
  }

  /**
   * Выводит динамический крон для всех проектов и ProjectManager'а
   */
  function a_cron() {
    print `pm localProjects cron`;
    if ($this->config['stat']) print "10 */1 * * * pm localServer updateStat\n";
  }

  /**
   * Очищает логи со всеми ошибками на сервере
   */
  function a_clearErrors() {
    chdir(NGN_ENV_PATH.'/run');
    Cli::shell('php run.php "(new AllErrors)->clear()"');
    `pm localProjects cc`;
  }

}
