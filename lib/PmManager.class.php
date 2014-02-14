<?php

class PmManager extends CliHelpOptions {

  static $tempPath, $downloadPath;

  /**
   * @return Tgz
   */
  static function getTgz() {
    // return new Tgz', ['tempFolder' => self::$tempPath]);
  }

  function prefix() {
    return 'pm';
  }

  protected function extraHelp() {
    print "---------\nprojects:\n";
    print implode(', ', Arr::get((new PmLocalProjectRecords)->getRecords(), 'name'))."\n";
  }
  
}

PmManager::$tempPath = NGN_ENV_PATH.'/temp/pm/'.Misc::randString(10);
PmManager::$downloadPath = NGN_ENV_PATH.'/download';
