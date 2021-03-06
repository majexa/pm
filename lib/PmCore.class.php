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

  static function cmd($cmd, $output = true) {
    if ($output and !getConstant('OUTPUT_DISABLE')) output('Cmd: '.$cmd, $output);
    $r = exec($cmd, $a, $exitCode);
    if ($exitCode) exit($exitCode);
    if ($output and !getConstant('OUTPUT_DISABLE') and $r) output("Cmd output: $r", $output);
    return $output;
  }

  static function cmdSuper($cmd) {
    self::cmd((O::get('PmLocalServerConfig')['os'] == 'linux' ? 'sudo ' : '').$cmd, true);
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

  static function remoteSshCommand(PmRemoteServerConfig $config, $cmd, $output = true) {
    $cmd = self::prepareCmd($config, $cmd);
    output("SSH: $cmd");
    if (getOS() == 'win') {
      // sys("plink -ssh -pw {$config->r['sshPass']} {$config->r['sshUser']}@".$config->r['host']." ".$cmd, true);
      $cmd = ($scp ? 'scp' : 'ssh')." {$config->r['sshUser']}".(isset($config->r['sshPass']) ? ':'.$config->r['sshPass'] : '')."@".$config->r['host']." ".$cmd;
      return shell_exec($cmd);
    }
    else {
      (new ShellSshKeyUploader(new ShellSshPasswordCmd([
        'host' => $config['host'],
        'pass' => $config['sshPass'],
        'user' => $config['sshUser']
      ])))->upload();
      return (new ShellSshCmd([
        'host' => $config['host'],
        'user' => $config['sshUser']
      ]))->cmd($cmd, $output);
    }
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
    $userHome = dirname(NGN_ENV_PATH);
    foreach (glob('{'.NGN_ENV_PATH."/*,$userHome/*}", GLOB_BRACE | GLOB_ONLYDIR) as $v) {
      if (file_exists("$v/web")) {
        self::$systemWebFolders[basename($v)] = "$v/web";
        continue;
      }
      if (file_exists("$v/.web")) {
        self::$systemWebFolders[basename($v)] = $v;
        continue;
      }
    }
    self::$systemWebFolders['default'] = NGN_PATH.'/defaultVhost';
    return self::$systemWebFolders;
  }

}

PmCore::check();