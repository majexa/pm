<?php

/**
 * Управление сервером на уровне проектов
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

  /**
   * Отображает все виртуальные хосты веб-сервера
   */
  function a_showHosts() {
    foreach ($this->getRecords() as $v) print "* {$v['domain']}\n";
  }

  /**
   * Апдейтит виртуальные хосты на веб-сервере
   */
  function a_updateHosts() {
    $this->updateHosts()->restart();
    $this->a_showHosts();
  }

  /**
   * Создаёт проект
   *
   * @options name, domain, @type
   */
  function a_createProject() {
    if ($this->options['domain'] == 'default') {
      $this->options['domain'] = $this->options['name'].'.'.$this->config['baseDomain'];
    }
    PmLocalProjectCore::create($this->options);
    PmWebserver::get()->restart();
  }

  /**
   * Создаёт проект, если его ещё нет или если его тип отличается от текущего
   *
   * @options name, domain, @type
   */
  function a_replaceProjectOnDiff() {
    if (($record = (new PmLocalProjectRecords())->getRecord($this->options['name']))) {
      if ($record['type'] != $this->options['type']) {
        $this->a_deleteProject();
        $this->a_createProject();
        output("Project created");
      } else {
        output("Same project already exists");
      }
    } else {
      $this->a_createProject();
      output("Project created");
    }
  }

  /**
   * Удаляет проект, только если он существует
   *
   * @options name
   */
  function a_deleteProject() {
    if (!(new PmLocalProjectRecords())->getRecord($this->options['name'])) {
      output("Project {$this->options['name']} does not exists");
      return;
    }
    (new PmLocalProject($this->options))->a_delete();
  }

  static function helpOpt_type() {
    return array_keys(PmCore::types());
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

  function systemDomain($name) {
    if ($name == 'dns') {
      return $name.'.'.PmCore::getLocalConfig()['dnsBaseDomain'];
    }
    return $name.'.'.PmCore::getLocalConfig()['baseDomain'];
  }

  protected function getRecords() {
    $records = [];
    foreach (PmCore::getSystemWebFolders() as $name => $webroot) $records[] = [
      'name'   => $name,
      'domain' => $this->systemDomain($name)
    ];
    $records = array_merge($records, (new PmLocalProjectRecords)->getRecords());
    foreach ($records as $v) {
      PmLocalProjectFs::updateConstant($this->config['projectsPath']."/{$v['name']}", 'more', 'SITE_DOMAIN', $v['domain'], false);
    }
    return $records;
  }

  function updateHosts() {
    $records = $this->getRecords();
    return PmWebserver::get()->regen($records);
  }

  /**
   * Создает файл дампа базы данных со структурой девственного проекта
   */
  function a_createDummyDump() {
    copy(PmCore::prepareDummyDbDump(), (new PmLocalServerConfig())->r['ngnPath'].'/dummy.sql');
  }

  /*
  function a_archEnv() {
    $this->a_createDummyDump();
    $ngnEnvPath = (new PmLocalServerConfig())->r['ngnEnvPath'];
    $this->addToArch($ngnEnvPath.'/dummy.sql');
    $this->addToArch($ngnEnvPath.'/dummyProject');
    $this->addToArch($ngnEnvPath.'/billing');
    $this->addToArch($ngnEnvPath.'/config');
    $this->addToArch($ngnEnvPath.'/fish');
    $this->addToArch($ngnEnvPath.'/install-dev-env');
    $this->addToArch($ngnEnvPath.'/install-env');
    $this->addToArch($ngnEnvPath.'/ngn');
    $this->addToArch($ngnEnvPath.'/pm');
    $this->addToArch($ngnEnvPath.'/run');
    $this->addToArch($ngnEnvPath.'/tests');
    $this->addToArch(Dir::make(PmManager::$tempPath.'/logs'));
    $this->addToArch(Dir::make(PmManager::$tempPath.'/temp'));
    $arch = $this->addToArch(Dir::make(PmManager::$tempPath.'/backup'));
    rename($arch, $ngnEnvPath.'/ngn-env.zip');
  }
  */

  protected function addToArch($what) {
    return Zip::add(PmManager::$tempPath.'/ngn-env.zip', $what);
  }

  /*
  function a_updateBuild() {
    Dir::$lastModifExcept[] = 'version.php';
    $ngnPath = NGN_PATH;
    $curNgnTstamp = Dir::getLastModifTime($ngnPath);
    $storedNgnTstamp = file_get_contents($ngnPath.'/tstamp');
    if ($storedNgnTstamp < $curNgnTstamp) {
      file_put_contents($ngnPath.'/tstamp', $curNgnTstamp);
      $c = Config::getConstants($ngnPath.'/config/version.php');
      $c['BUILD_TIME'] = $curNgnTstamp;
      $c['BUILD']++;
      Config::updateConstants($ngnPath.'/config/version.php', $c);
      output('Ngn timestamp changed. New build: '.$c['BUILD']);
    }
  }
  */

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
