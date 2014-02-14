<?php

class PmLocalProjects extends CliHelpMultiWrapper {

  protected $records;

  function init() {
    $this->records = (new PmLocalProjectRecords)->getRecords();
  }

  /*
  function a_capture() {
    $config = new PmLocalServerConfig;
    $urls = implode(',', Arr::get($this->records, 'domain'));
    Cli::shell("phantomjs {$config['scriptsPath']}/capture.js $urls {$config['pmPath']}/captures");
  }
  */

}
