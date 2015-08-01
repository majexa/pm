<?php

class PmRemoteProject {
  use Options;
  /**
   * @var PmRemoteProjectConfig
   */
  protected $config;
  /**
   * @var PmLocalServerConfig
   */
  protected $localServerConfig;

  function __construct($serverName, $domain, array $options = []) {
    $this->setOptions($options);
    $this->config = new PmRemoteProjectConfig($serverName, $domain);
    $this->localServerConfig = (new PmLocalServerConfig());
  }

  /**
   * @return PmRemoteServerDev
   */
  function getServer() {
    return O::get('PmRemoteServerDev', $this->config->serverConfig());
  }

  /**
   * Imports database on remote server from defined dump file
   *
   * @options file
   */
  function a_importDb() {
    Arr::checkEmpty($this->options, 'file');
    $this->importDbFromLocal($this->options['file']);
  }

  function importFsFromLocal($tempProjectWebroot) {
    if ($this->config->r['webserver'] == 'nginx') File::delete($tempProjectWebroot.'/.htaccess');
    PmLocalProjectFs::updateDbConfig($tempProjectWebroot, $this->config->r);
    PmLocalProjectFs::updateConstant($tempProjectWebroot, 'site', 'SITE_DOMAIN', $this->config->r['domain']);
    PmLocalProjectFs::updateConstant($tempProjectWebroot, 'core', 'PROJECT_KEY', $this->config->r['name']);
    $oS = $this->getServer();
    $this->updateIndex($tempProjectWebroot);
    $oS->uploadFolder($tempProjectWebroot, 'temp');
    $oS->remoteSshCommand("chmod -R 0777 {$this->config->r['webroot']}/site");
    $oS->remoteSshCommand("rm -r {$this->config->r['webroot']}");
    $oS->remoteSshCommand("mkdir {$this->config->r['webroot']}");
    $oS->remoteSshCommand("cp -r {$this->config->r['tempPath']}/webroot/* {$this->config->r['webroot']}");
  }

//  function importFs($tempProjectWebroot) {
//    $oS = $this->getServer();
//    $oS->remoteSshCommand("rm -r {$this->config->r['webroot']}");
//    $oS->remoteSshCommand("mkdir -p {$this->config->r['webroot']}");
//    $oS->remoteSshCommand("cp -r $tempProjectWebroot/* {$this->config->r['webroot']}");
//  }

//  protected function updateIndex($webroot) {
//    Config::updateConstant($webroot.'/index.php', 'NGN_PATH', $this->config->r['ngnPath']);
//    //Config::updateConstant($webroot.'/index.php', 'VENDORS_PATH', $this->oPC->r['vendorsPath']);
//  }

//  function a_updateNgn() {
//    $this->getServer()->uploadFolder2($this->localServerConfig->r['ngnPath'], $this->config->r['webroot']);
//    Url::touch('http://'.$this->config->name.'/s2/cc');
//  }

//  function a_enableOwnNgn() {
//    $this->a_updateNgn();
//    copy($this->localServerConfig->r['dummyProjectPath'].'/index.php', PmManager::$tempPath.'/index.php');
//    $this->updateIndex(PmManager::$tempPath);
//    Config::updateConstant(PmManager::$tempPath.'/index.php', 'NGN_PATH', $this->config->r['webroot'].'/ngn');
//    $this->getServer()->uploadFile2(PmManager::$tempPath.'/index.php', $this->config->r['webroot']);
//  }

//  function a_disableOwnNgn() {
//    $this->getServer()->remoteSshCommand('pm -r '.$this->config->r['webroot'].'/ngn');
//    copy($this->localServerConfig->r['dummyProjectPath'].'/index.php', PmManager::$tempPath.'/index.php');
//    $this->updateIndex(PmManager::$tempPath);
//    $this->getServer()->uploadFile2(PmManager::$tempPath.'/index.php', $this->config->r['webroot'].'/index.php');
//  }

  function importDbFromLocal($dumpFile) {
    $relFilePath = $this->getServer()->uploadFileArchived($dumpFile, 'temp');
    $this->importDb('$ngnEnvPath'.$relFilePath);
  }

  function importDb($dumpPath) {
    $this->getServer()->remoteMysqlImport($this->config->r['dbName'], $dumpPath);
  }

  function downloadFs() {
    $this->getServer()->downloadFolder($this->config['webroot'], [
      '.git',
      'temp/*',
      'u/*',
      'logs/*',
      'data/*',
      //'data/cache/*',
      //'data/ddiCache/*',
      //'data/state/*'
    ]);
    output("Cleaned project folder downloaded");
    $partialUFolder = $this->getServer()->remoteSshCommand("pm localProject exportUFolder {$this->config['name']}");
    $projectFolder = $this->getServer()->downloadFolder($partialUFolder, [], false);
    output("Project upload folder downloaded");
    return $projectFolder;
  }

  /**
   * Скачивает дамп базы проекта и возвращает путь к файлу с дампом
   *
   * @return string
   */
  function downloadDb() {
    $remoteDumpFile = $this->getServer()->remoteSshCommand('pm localProject exportDb '.$this->config['name']);
    return $this->getServer()->downloadFile($remoteDumpFile);
  }

}