<?php

class PmRemoteProject {
  use Options;

  /**
   * @var PmRemoteProjectConfig
   */
  protected $projectConfig;

  /**
   * @var PmLocalServerConfig
   */
  protected $localServerConfig;

  function __construct($serverName, $domain, array $options = []) {
    $this->setOptions($options);
    $this->projectConfig = new PmRemoteProjectConfig($serverName, $domain);
    $this->localServerConfig = (new PmLocalServerConfig());
  }

  /**
   * @return PmRemoteServerDev
   */
  function getServer() {
    return O::get('PmRemoteServerDev', $this->projectConfig->serverConfig());
  }

  /**
   * @options file
   */
  function a_importDb() {
    Arr::checkEmpty($this->options, 'file');
    $this->importDbFromLocal($this->options['file']);
  }

  function importFsFromLocal($tempProjectWebroot) {
    if ($this->projectConfig->r['webserver'] == 'nginx') File::delete($tempProjectWebroot.'/.htaccess');
    PmLocalProjectFs::updateDbConfig($tempProjectWebroot, $this->projectConfig->r);
    PmLocalProjectFs::updateConstant($tempProjectWebroot, 'site', 'SITE_DOMAIN', $this->projectConfig->r['domain']);
    PmLocalProjectFs::updateConstant($tempProjectWebroot, 'core', 'PROJECT_KEY', $this->projectConfig->r['projectKey']);
    $oS = $this->getServer();
    $this->updateIndex($tempProjectWebroot);
    $oS->uploadFolder($tempProjectWebroot, 'temp');
    $oS->remoteSshCommand("chmod -R 0777 {$this->projectConfig->r['webroot']}/site");
    $oS->remoteSshCommand("rm -r {$this->projectConfig->r['webroot']}");
    $oS->remoteSshCommand("mkdir {$this->projectConfig->r['webroot']}");
    $oS->remoteSshCommand("cp -r {$this->projectConfig->r['tempPath']}/webroot/* {$this->projectConfig->r['webroot']}");
  }

  function importFs($tempProjectWebroot) {
    $oS = $this->getServer();
    $oS->remoteSshCommand("rm -r {$this->projectConfig->r['webroot']}");
    $oS->remoteSshCommand("mkdir -p {$this->projectConfig->r['webroot']}");
    $oS->remoteSshCommand("cp -r $tempProjectWebroot/* {$this->projectConfig->r['webroot']}");
  }

  protected function updateIndex($webroot) {
    Config::updateConstant($webroot.'/index.php', 'NGN_PATH', $this->projectConfig->r['ngnPath']);
    //Config::updateConstant($webroot.'/index.php', 'VENDORS_PATH', $this->oPC->r['vendorsPath']);
  }

  function a_updateNgn() {
    $this->getServer()->uploadFolder2($this->localServerConfig->r['ngnPath'], $this->projectConfig->r['webroot']);
    Url::touch('http://'.$this->projectConfig->name.'/s2/cc');
  }

  function a_enableOwnNgn() {
    $this->a_updateNgn();
    copy($this->localServerConfig->r['dummyProjectPath'].'/index.php', PmManager::$tempPath.'/index.php');
    $this->updateIndex(PmManager::$tempPath);
    Config::updateConstant(PmManager::$tempPath.'/index.php', 'NGN_PATH', $this->projectConfig->r['webroot'].'/ngn');
    $this->getServer()->uploadFile2(PmManager::$tempPath.'/index.php', $this->projectConfig->r['webroot']);
  }

  function a_disableOwnNgn() {
    $this->getServer()->remoteSshCommand('pm -r '.$this->projectConfig->r['webroot'].'/ngn');
    copy($this->localServerConfig->r['dummyProjectPath'].'/index.php', PmManager::$tempPath.'/index.php');
    $this->updateIndex(PmManager::$tempPath);
    $this->getServer()->uploadFile2(PmManager::$tempPath.'/index.php', $this->projectConfig->r['webroot'].'/index.php');
  }

  function importDbFromLocal($dumpFile) {
    $relFilePath = $this->getServer()->uploadFileArchived($dumpFile, 'temp');
    $this->importDb('$ngnEnvPath'.$relFilePath);
  }

  function importDb($dumpPath) {
    $this->getServer()->remoteMysqlImport($this->projectConfig->r['dbName'], $dumpPath);
  }

  function archiveFs() {
    return $this->getServer()->archive($this->projectConfig->r['webroot']);
  }

  function genSshKey($remoteServer) {
  }

  /**
   * @param PmRemoteServerDev Сервер, с которого необходимо скачивать
   */
  function downloadFs(PmRemoteProject $oFromProject) {
    return $this->getServer()->downloadFolder($oFromProject->getServer(), $oFromProject->projectConfig->r['webroot'], $this->projectConfig->r['tempPath']);
  }

  function downloadDb(PmRemoteProject $oFromProject) {
    return $this->getServer()->downloadDb($oFromProject->getServer(), $oFromProject->projectConfig->r['dbName']);
  }

  function localDownloadFs() {
    return $this->getServer()->localDownloadProjectFolder($this->projectConfig->r['webroot']);
  }

  function localDownloadDb() {
    return $this->getServer()->localDownloadDb($this->projectConfig->r['dbName']);
  }

}
