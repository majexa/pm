<?php

/**
 * Всё, что касается проектов в папке projects
 */
class PmManager extends CliAccessOptionsAbstract {

  static $tempPath, $downloadPath;

  function prefix() {
    return 'pm';
  }

  protected function extraHelp() {
    print "---------\nprojects:\n";
    print implode(', ', Arr::get((new PmLocalProjectRecords)->getRecords(), 'name'))."\n";
  }

  protected function init() {
    Err::setEntryCmd($this->prefix().' '.implode(' ', array_slice($_SERVER['argv'], 1)));
  }
  
}

PmManager::$tempPath = NGN_ENV_PATH.'/temp/pm';
Dir::make(PmManager::$tempPath);
PmManager::$downloadPath = NGN_ENV_PATH.'/download';
