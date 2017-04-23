<?php

class PmLocalProjectFs {

  protected $config;

  function __construct(PmProjectConfig $config) {
    $this->config = $config;
  }

  function prepareAndCopyToWebroot() {
    Dir::copy($this->prepareDummyProject(), $this->config->r['webroot']);
  }

  /**
   * Копирует проект-пустышку во временный каталог и изменяет файлы в зависимости
   * от параметров проекта
   *
   * @return string
   */
  function prepareDummyProject() {
    $tempDummyFolder = PmManager::$tempPath.'/dummy';
    output("Prepare dummy project '{$this->config->r['dummyProjectPath']}' -> '$tempDummyFolder'");
    Dir::copy($this->config['dummyProjectPath'], $tempDummyFolder);
    Dir::remove($tempDummyFolder.'/.git');
    self::updateConstant($tempDummyFolder, 'core', 'PROJECT_KEY', $this->config['name']);
    self::updateConstant($tempDummyFolder, 'more', 'SITE_DOMAIN', $this->config['domain']);
    //$this->config['type']
    //new DbP
    //self::updateConstant($tempDummyFolder, 'site', 'LAST_DB_PATCH', (new DbPatcher())->getNgnLastPatchN());
    // update index.php constants
    //Config::updateConstant($tempDummyFolder.'/index.php', 'NGN_PATH', $this->config->r['ngnPath']);
    //Config::updateConstant($tempDummyFolder.'/index.php', 'VENDORS_PATH', $this->config->r['vendorsPath']);
    //self::updateConstant($tempDummyFolder, 'site', 'SITE_TITLE', $this->config['name']);
    //$dbPatcher = new DbPatcher();
    //$dbPatcher->noCache = true;
    //self::updateConstant($tempDummyFolder, 'site', 'LAST_FILE_PATCH', (new FilePatcher())->getNgnLastPatchN());
    //self::updateDbConfig($tempDummyFolder, $this->config->r);
    return $tempDummyFolder;
  }

  // ------------------ static ---------------------

  static function updateConstant($webroot, $type, $constName, $consValue, $strict = true) {
    $file = "$webroot/site/config/constants/$type.php";
    if (!$strict and !file_exists($file)) return false;
    Config::updateConstant($file, $constName, $consValue);
    return true;
  }

  static function replaceConstant($webroot, $type, $constName, $constValue, $strict = true) {
    $file = "$webroot/site/config/constants/$type.php";
    if (!$strict and !file_exists($file)) {
      file_put_contents($file, "<?php\n\n");
    }
    Config::replaceConstant($file, $constName, $constValue);
    return true;
  }

  static function updateVar($webroot, $type, $subKey, $value) {
    Config::updateSubVar($webroot.'/site/config/vars/'.$type.'.php', $subKey, $value);
  }

  /**
   * Обновляет конфигурацию БД
   *return new PmLocalProjectDev($oPC);
   * @param   string  Корневой каталог проекта
   * @param   array   Массив с данными проекта
   */
  static function updateDbConfig($webroot, array $p) {
    self::updateConstant($webroot, 'database', 'DB_NAME', $p['dbName']);
    self::updateConstant($webroot, 'database', 'DB_USER', $p['dbUser']);
    self::updateConstant($webroot, 'database', 'DB_PASS', $p['dbPass']);
    self::updateConstant($webroot, 'database', 'DB_HOST', $p['dbHost']);
  }

}