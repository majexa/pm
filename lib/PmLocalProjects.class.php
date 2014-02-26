<?php

class PmLocalProjects extends CliHelpOptionsMultiWrapper {

  protected function records() {
    return (new PmLocalProjectRecords)->getRecords();
  }

  function a_daemons() {
    foreach (PmLocalProject::$daemonNames as $name) {
      foreach (glob("/etc/init.d/*-$name") as $file) Cli::shell("sudo rm $file");
    }
  }

  /*
  function a_capture() {
    $config = new PmLocalServerConfig;
    $urls = implode(',', Arr::get($this->records, 'domain'));
    Cli::shell("phantomjs {$config['scriptsPath']}/capture.js $urls {$config['pmPath']}/captures");
  }
  */

}
