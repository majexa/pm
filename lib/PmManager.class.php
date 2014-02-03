<?php

class PmManager extends CliHelp {

  static $tempPath, $downloadPath;

  /**
   * @return Tgz
   */
  static function getTgz() {
    //return new Tgz', ['tempFolder' => self::$tempPath]);
  }

  protected function prefix() {
    return 'pm';
  }

  protected function extraHelp() {
    print "---------\nprojects:\n";
  }
  
}

PmManager::$tempPath = NGN_ENV_PATH.'/temp/pm/'.Misc::randString(10);
PmManager::$downloadPath = NGN_ENV_PATH.'/download';
