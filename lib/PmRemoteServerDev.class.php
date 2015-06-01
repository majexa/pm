<?php

class PmRemoteServerDev {
  use Options;

  /**
   * @var PmLocalServerConfigDev
   */
  public $locaConfig;

  /**
   * @var PmRemoteServerConfig
   */
  public $remoteConfig;

  function __construct(PmRemoteServerConfig $remoteServerConfig, array $options = []) {
    $this->locaConfig = O::get('PmLocalServerConfigDev');
    $this->remoteConfig = $remoteServerConfig;
    $this->setOptions($options);
  }

  function a_updateConfig() {
    $this->uploadFileRenamed($this->remoteConfig->getFile(), 'server.php', 'config');
    $this->uploadFolder2($this->locaConfig->r['pmPath'].'/defaultWebserverRecords', $this->remoteConfig->r['ngnEnvPath']);
  }

  function a_firstEnvSetup() {
    $this->remoteSshCommand('mkdir -p '.$this->remoteConfig->r['backupPath']);
    foreach ([
      'projectsPath',
      'tempPath',
      'logsPath',
      'configPath',
      'webserverProjectsConfigFolder'
    ] as $v) {
      $this->remoteSshCommand('mkdir '.$this->remoteConfig->r[$v]);
    }
    $this->a_updateEnv();
  }

  function a_updateEnv() {
    $this->a_updateConfig();
    $this->a_updateVendors();
    $this->a_updatePm();
    $this->a_updateRun();
    $this->a_updateScripts();
    $this->updateEnvFolder('dummyProject');
    $this->a_updateDummyDbDump();
  }

  function a_updateVendors() {
    $this->updateEnvFolder('vendors');
  }

  function a_updateDummyProject() {
    Dir::copy($this->locaConfig->r['dummyProjectPath'], PmManager::$tempPath.'/dummyProject');
    $this->updateNgnAndVendorsConstants(PmManager::$tempPath.'/dummyProject/index.php');
    $this->uploadFolderToRoot(PmManager::$tempPath.'/dummyProject');
  }

  function a_downloadNgn() {
    Dir::move($this->downloadFolder($this->remoteConfig->r['ngnPath']), PmManager::$downloadPath.'/ngn');
    output("downloaded to: ".PmManager::$downloadPath.'/ngn');
  }

  function a_updateMyadmin() {
    $this->updateEnvFolder('myadmin');
  }

  function a_updateDummyDbDump() {
    $this->uploadFile(PmCore::prepareDummyDbDump());
  }

  /**
   * @options folder
   */
  function a_updateEnvFolder() {
    Arr::checkEmpty($this->options, 'folder');
    $this->updateEnvFolder($this->options['folder']);
  }

  protected function updateEnvFolder($folderName) {
    $this->uploadFolderToRoot($this->locaConfig->r[$folderName.'Path']);
    $this->makeExecutables($folderName);
  }

  protected $executabels = [
    'pm'  => ['pm'],
    'run' => ['run.php', 'site.php'],
  ];

  protected function makeExecutables($folderName) {
    if (isset($this->executabels[$folderName])) foreach ($this->executabels[$folderName] as $filename) $this->remoteSshCommand('chmod +x '.$this->remoteConfig->r[$folderName.'Path'].'/'.$filename);
  }

  public $masterProjectDomain;

  function a_updatePm() {
    if (isset($this->masterProjectDomain)) throw new Exception('define $this->masterProjectDomain');
    Dir::copy($this->locaConfig->r['pmPath'], PmManager::$tempPath.'/pm');
    $tempPmPath = PmManager::$tempPath.'/pm';
    File::replaceTttt($tempPmPath.'/pm', $this->remoteConfig->r);
    $this->updateNgnAndVendorsConstants($tempPmPath.'/common-init.php');
    $this->updateNgnAndVendorsConstants($tempPmPath.'/web/init.php');
    PmLocalProjectFs::updateDbConfig($tempPmPath.'/web', O::get('PmRemoteProjectConfigDev', $this->remoteConfig->name, $this->masterProjectDomain)->r);
    $this->uploadFolderToRoot($tempPmPath);
    $this->remoteSshCommand('chmod -R 0777 '.$this->remoteConfig->r['pmPath'].'/web');
    $this->makeExecutables('pm');
    $this->remoteSshCommand($this->remoteConfig->r['runPath'].'/run.php genPmPassword');
  }

  function a_updateRun() {
    Dir::copy($this->locaConfig->r['runPath'], PmManager::$tempPath.'/run');
    $tempRunPath = PmManager::$tempPath.'/run';
    $this->updateNgnAndVendorsConstants($tempRunPath.'/run.php');
    $this->updateNgnAndVendorsConstants($tempRunPath.'/projectStandAloneInit.php');
    $p = explode('{domain}', $this->remoteConfig->r['webroot']);
    $webroot = "'{$p[0]}'.\$_SERVER['argv'][1]".(!empty($p[1]) ? "'{$p[1]}'" : '');
    Config::updateConstant($tempRunPath.'/projectStandAloneInit.php', 'WEBROOT_PATH', $webroot, false);
    $this->uploadFolderToRoot($tempRunPath);
    $this->makeExecutables('run');
  }

  function a_updateScripts() {
    $this->executabels['scripts'] = Dir::files($this->locaConfig->r['scriptsPath']);
    $this->updateEnvFolder('scripts');
  }

  /**
   * @options domain
   */
  function a_updateInstallEnv() {
    Arr::checkEmpty($this->options, 'domain');
    Dir::copy($this->locaConfig->r['ngnEnvPath'].'/install-env', PmManager::$tempPath.'/install-env');
    foreach (glob(PmManager::$tempPath.'/install-env/*') as $file) {
      $c = file_get_contents($file);
      $c = str_replace('{domain}', $this->options['domain'], $c);
      $c = str_replace('{user}', $this->remoteConfig->r['sshUser'], $c);
      file_put_contents($file, $c);
    }
    $this->uploadFolder(PmManager::$tempPath.'/install-env', 'projects/'.$this->options['domain']);
  }

  protected function updateNgnAndVendorsConstants($file) {
    Config::updateConstant($file, 'NGN_PATH', $this->remoteConfig->r['ngnPath']);
    //Config::updateConstant($file, 'VENDORS_PATH', $this->oRSCD->r['vendorsPath']);
  }

  /**
   * @options folder
   */
  function a_uploadFolderToRoot() {
    Arr::checkEmpty($this->options, 'folder');
    $this->uploadFolderToRoot($this->locaConfig->r['ngnEnvPath'].'/'.$this->options['folder']);
  }

  protected function uploadFolderToRoot($folder) {
    $folderName = basename($folder);
    $ftpRoot = $this->ftpInit();
    $this->ftp->upload(Zip::archive(PmManager::$tempPath, $folder, $folderName.'.zip'), $ftpRoot);
    $this->remoteSshCommand('rm -r $ngnEnvPath/'.$folderName);
    $this->remoteSshCommand('unzip -o $ngnEnvPath/'.$folderName.'.zip -d $ngnEnvPath');
    $this->remoteSshCommand('rm $ngnEnvPath/'.$folderName.'.zip');
  }

  function uploadFolder($folder, $fromRootPath) {
    $folderName = basename($folder);
    $ftpRoot = $this->ftpInit();
    $this->ftp->upload(Zip::archive(PmManager::$tempPath, $folder, "$folderName.zip"), $ftpRoot.'/'.$fromRootPath);
    $this->remoteSshCommand("rm -r \$ngnEnvPath/$fromRootPath/$folderName");
    $this->remoteSshCommand("unzip -o \$ngnEnvPath/$fromRootPath/$folderName.zip -d \$ngnEnvPath/$fromRootPath");
    $this->remoteSshCommand("rm \$ngnEnvPath/$fromRootPath/$folderName.zip");
  }

  function uploadFolder2($folder, $path) {
    $folderName = basename($folder);
    $this->ftpInit();
    $this->ftp->upload(Zip::archive(PmManager::$tempPath, $folder, "$folderName.zip"), $path);
    $this->remoteSshCommand("rm -r $path/$folderName");
    $this->remoteSshCommand("unzip -o $path/$folderName.zip -d $path");
    $this->remoteSshCommand("rm $path/$folderName.zip");
  }

  protected function uploadFileRenamed($file, $newname, $toFolder = '') {
    $tempFile = PmManager::$tempPath.'/'.$newname;
    copy($file, $tempFile);
    $this->uploadFile($tempFile, $toFolder);
  }

  function uploadFile($file, $toFolder = '') {
    $ftpRoot = $this->ftpInit();
    $this->ftp->upload($file, $ftpRoot.($toFolder ? '/'.$toFolder : $toFolder));
  }

  function uploadFile2($file, $path) {
    $this->ftpInit();
    $this->ftp->upload($file, $path);
  }

  function uploadFileArchived($file, $toFolder = '') {
    $archfilename = basename($file).'.zip';
    $toFolder = Misc::trimSlashes($toFolder);
    if ($toFolder) $toFolder = '/'.$toFolder;
    $archive = Zip::archive(PmManager::$tempPath, $file, $archfilename);
    $this->uploadFile($archive, $toFolder);
    $this->remoteSshCommand("unzip -o \$ngnEnvPath$toFolder/$archfilename -d \$ngnEnvPath$toFolder");
    $this->remoteSshCommand("rm \$ngnEnvPath$toFolder/$archfilename");
    return $toFolder.'/'.basename($file);
  }

  /**
   * @var Ftp
   */
  protected $ftp;

  protected function ftpInit() {
    $this->ftp = new Ftp();
    $this->ftp->server = $this->remoteConfig->r['host'];
    $this->ftp->user = $this->remoteConfig->r['ftpUser'];
    $this->ftp->password = $this->remoteConfig->r['ftpPass'];
    $this->ftp->tempPath = PmManager::$tempPath;
    if (!$this->ftp->connect()) throw new Exception('Could not connect');
    return $this->remoteConfig->r['ftpRoot'];
  }

  function remoteSshCommand($cmd) {
    return PmCore::remoteSshCommand($this->remoteConfig, $cmd);
  }

  function remoteScpCommand($cmd) {
    return PmCore::remoteSshCommand($this->remoteConfig, $cmd, true);
  }

  protected function getMysqlAuthStr() {
    return Mysql::auth($this->remoteConfig->r);
  }

  function remoteMysqlImport($dbName, $file) {
    $u = $this->getMysqlAuthStr();
    $this->remoteSshCommand("mysqladmin --force $u drop $dbName");
    $this->remoteSshCommandFile("
mysql $u -e \"CREATE DATABASE $dbName DEFAULT CHARACTER SET ".DB_CHARSET." COLLATE ".DB_COLLATE."\"
mysql $u --default_character_set utf8 $dbName < $file
");
  }

  function remoteSshCommandFile($cmd) {
    file_put_contents(PmManager::$tempPath.'/cmd', PmCore::prepareCmd($this->remoteConfig, $cmd));
    $this->uploadFile(PmManager::$tempPath.'/cmd', 'temp');
    $this->remoteSshCommand('chmod +x '.$this->remoteConfig->r['tempPath'].'/cmd');
    $this->remoteSshCommand($this->remoteConfig->r['tempPath'].'/cmd');
    $this->remoteSshCommand('rm '.$this->remoteConfig->r['tempPath'].'/cmd');
  }

  function archive($remotePath, array $excludeDirs = []) {
    $name = basename($remotePath);
    $filename = basename($remotePath).'.tgz';
    $archive = "{$this->remoteConfig->r['tempPath']}/$filename";
    $this->remoteSshCommand("rm $archive");
    $this->remoteSshCommand("tar ".St::enum($excludeDirs, '', '` --exclude=`.$v')." -C ".dirname($remotePath)." -czf $archive $name");
    return $archive;
  }

  /**
   * Скачивает каталог с удаленного сервера на текущий
   *
   * @param PmRemoteServerDev $remoteServer
   * @param string $fromPath Сервер, с которого нужно скачать
   * @param string $toFolder Каталог, который необходимо скачать
   * @return string
   */
  function _downloadFolder(PmRemoteServerDev $remoteServer, $fromPath, $toFolder) {
    output("Downloading '$fromPath' to '$toFolder'...");
    $fromArchive = $remoteServer->archive($fromPath);
    $this->remoteSshCommand("mkdir -p $toFolder");
    $toArchive = $toFolder.'/'.basename($fromArchive);
    $this->__downloadFile($remoteServer, $fromArchive, $toArchive);
    $this->remoteSshCommand("tar -C $toFolder -xvf $toArchive");
    $this->remoteSshCommand("rm $toArchive");
    return $toFolder.'/'.basename($fromPath);
  }

  function __downloadFile(PmRemoteServerDev $oFromServer, $fromPath, $toPath) {
    $r = $oFromServer->remoteConfig->r;
    $this->remoteSshCommandFile("lftp -u {$r['ftpUser']},{$r['ftpPass']} {$r['host']} -e \"get $fromPath -o $toPath; exit\"");
  }

  function _downloadFile(PmRemoteServerDev $srcServer, $fromPath, $toFolder) {
    $this->__downloadFile($srcServer, $srcServer->archive($fromPath), "$toFolder/arch.tgz");
    $this->remoteSshCommand("tar -C $toFolder -xvf $toFolder/arch.tgz");
    $this->remoteSshCommand("rm $toFolder/arch.tgz");
  }

  function _downloadDb(PmRemoteServerDev $srcServer, $dbName) {
    $remoteDumpPath = $srcServer->dumpDb($dbName);
    $this->_downloadFile($srcServer, $remoteDumpPath, $this->remoteConfig->r['tempPath']);
    return $this->remoteConfig->r['tempPath'].'/'.basename($remoteDumpPath);
  }

  function downloadProjectFolder($webroot) {
    //die2('!');
    print $this->remoteSshCommand('pm');
    return;
    //return $this->downloadFolder($webroot.'/u');
    return $this->downloadFolder($webroot, [
      'temp/*',
      'u/*',
      'cache/*',
      'ddiCache/*',
      'state/*'
    ]);
  }

  function downloadFolder($remotePath, array $exclude = []) {
    $remotePath = $this->archive($remotePath, $exclude);
    $name = basename($remotePath);
    $localArchive = './temp/'.$name;
    File::delete($localArchive);
    File::delete('./temp/'.$name);
    sys("scp user@majexa.ru:$remotePath $localArchive", true);
    sys("tar -xzf $localArchive -C ./temp", true);
    return './temp/'.str_replace('.tgz', '', $name);
  }

  function downloadFile($remotePath) {
    $name = basename($remotePath);
    $filename = basename($remotePath).'.tgz';
    $this->remoteSshCommand("cd ".dirname($remotePath)."; tar -czf $filename $name");
    $archiveName = $name.'.tgz';
    $localArchive = './temp/'.$archiveName;
    File::delete($localArchive);
    File::delete('./temp/'.$name);
    sys("scp user@majexa.ru:$remotePath.tgz $localArchive", true);
    sys("tar -xvzf $localArchive -C ./temp", true);
    return './temp/'.$name;
  }

  function dumpDb($dbName) {
    $remoteDumpFile = "{$this->remoteConfig->r['tempPath']}/$dbName";
    $this->remoteSshCommandFile("mysqldump {$this->getMysqlAuthStr()} $dbName > $remoteDumpFile");
    return $remoteDumpFile;
  }

  function downloadDb($dbName) {
    return $this->downloadFile($this->dumpDb($dbName));
  }

}
