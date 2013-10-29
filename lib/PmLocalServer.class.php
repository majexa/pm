<?php

class PmLocalServer extends ArrayAccessebleOptions {
use PmDatabase;

  protected $config;

  function init() {
    $this->config = new PmLocalServerConfig;
  }

  /**
   * @options name, domain, @type
   */
  function a_createProject() {
    PmLocalProjectCore::create($this->options);
    PmWebserver::get()->restart();
  }

  static function helpOpt_type() {
    return implode('|', array_keys(PmCore::config('types')));
  }

  /**
   * @options domain
   */
  function a_createHost() {
    PmDnsManager::get()->create($this->options['domain']);
  }

  /**
   * @options domain
   */
  function a_deleteHost() {
    PmDnsManager::get()->delete($this->options['domain']);
  }

  /**
   * @options dbName
   */
  function a_createDummyDb() {
    $this->createDb($this->options['dbName']);
    $this->importSqlDump($this->config['ngnEnvPath'].'/dummy.sql', $this->options['dbName']);
  }

  function a_updateHosts() {
    $this->updateHosts()->restart();
  }

  protected function systemDomain($name) {
    if ($name == 'dns') {
      return $name.'.'.PmCore::getLocalConfig()['dnsBaseDomain'];
    }
    return $name.'.'.PmCore::getLocalConfig()['baseDomain'];
  }

  function updateHosts() {
    foreach (PmCore::getSystemSubdomains() as $name) $records[] = [
      'name' => $name,
      'domain' => $this->systemDomain($name)
    ];
    $records = array_merge($records, (new PmLocalProjectRecords)->getRecords());
    PmDnsManager::get()->regen($records);
    return PmWebserver::get()->regen($records);
  }

  function a_createDummyDump() {
    copy(
      PmCore::prepareDummyDbDump(),
      (new PmLocalServerConfig())->r['ngnEnvPath'].'/dummy.sql'
    );
  }

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
  
  protected function addToArch($what) {
    return Zip::add(PmManager::$tempPath.'/ngn-env.zip', $what);
  }
  
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

}
