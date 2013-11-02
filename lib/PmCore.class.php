<?php

class PmCore {

  /*
   * Должно выполняться в самом начале
   */
  static function check() {
    Misc::checkConst('NGN_ENV_PATH');
  }

  static function getLocalConfig() {
    return include NGN_ENV_PATH.'/config/server.php';
  }

  static function cmdSuper($cmd) {
    sys(((new PmLocalServerConfig)['os'] == 'linux' ? 'sudo ' : '').$cmd);
  }

  static function prodDomain($domain) {
    return preg_replace('/^test\d*[.-](.*)$/', '$1', $domain);
  }

  static function testDomain($domain, $prefix = 'test') {
    $domain = self::prodDomain($domain);
    return count(explode('.', $domain)) <= 2 ? "$prefix.$domain" : "$prefix-$domain";
  }

  static function prepareCmd(PmRemoteServerConfig $oRSC, $cmd) {
    return str_replace('$ngnEnvPath', $oRSC->r['ngnEnvPath'], $cmd);
  }

  static function remoteSshCommand(PmRemoteServerConfig $config, $cmd) {
    $cmd = self::prepareCmd($config, $cmd);
    output("SSH: $cmd");
    if (getOS() == 'win') {
      sys("plink -ssh -pw {$config->r['sshPass']} {$config->r['sshUser']}@".$config->r['host']." ".$cmd, true);
    }
    else {
      sys('expect -c \'spawn ssh '.$config->r['sshUser'].'@'.$config->r['host'].' '.$cmd.'; expect password ; send "'.$config->r['sshPass'].'\n" ; interact\'', false);
    }
  }

  static function config($name) {
    return require dirname(__DIR__)."/config/$name.php";
  }

  static function prepareDummyDbDump() {
    $oSC = O::get('PmLocalServerConfig');
    if ($oSC->r['prototypeDb'] == 'file') {
      File::checkExists(NGN_ENV_PATH.'/dump.sql');
      return NGN_ENV_PATH.'/dump.sql';
    }
    output('Prepear dummy database dump');
    try {
      $db = new Db($oSC->r['dbUser'], $oSC->r['dbPass'], $oSC->r['dbHost'], $oSC->r['prototypeDb'], 'utf8');
    } catch (Exception $e) {
      throw new Exception("Prototype db '{$oSC->r['prototypeDb']}' does not exists");
    }
    $db->setErrorHandler('output');
    $dumper = new DbDumper($db);
    $dumper->setDroptables(true);
    $dumper->isDumpData = false;
    $dumper->excludeRule = '^.*dd_i_.*$';
    $dumper->createDump(PmManager::$tempPath.'/dummy.sql');
    return PmManager::$tempPath.'/dummy.sql';
  }

  static function getFields() {
    return [
      [
        'title' => 'Домен',
        'name'  => 'domain'
      ],
      [
        'title' => 'Название',
        'name'  => 'title'
      ],
      [
        'title' => 'Алиасы',
        'name'  => 'aliases'
      ],
    ];
  }

  static $systemWebFolders;

  static function getSystemWebFolders() {
    if (isset(self::$systemWebFolders)) return self::$systemWebFolders;
    self::$systemWebFolders = [];
    foreach (glob('{'.NGN_ENV_PATH.'/*,~/*}', GLOB_BRACE | GLOB_ONLYDIR) as $v) {
      self::$systemWebFolders[basename($v)] = file_exists("$v/.web");
    }
    return self::$systemWebFolders;
  }

  static function getSystemSubdomains() {
    return array_map(function($v) {
      return basename($v);
    }, self::getSystemWebFolders());
  }

}

PmCore::check();