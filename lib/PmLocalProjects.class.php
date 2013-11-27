<?php

class PmLocalProjects extends ArrayAccessebleOptions {

  static $set = true;
  protected $records;

  function init() {
    $this->records = (new PmLocalProjectRecords)->getRecords();
  }
  
  function action($action) {
    $method = "a_$action";
    if (method_exists($this, $method)) {
      $this->$method();
      return;
    }
    foreach ($this->records as $v) {
      $this->options['name'] = $v['name'];
      (new PmLocalProject($this->options))->{'a_'.$action}();
    }
  }

  function a_capture() {
    $config = new PmLocalServerConfig;
    $urls = implode(',', Arr::get($this->records, 'domain'));
    Cli::shell("phantomjs {$config['scriptsPath']}/capture.js $urls {$config['pmPath']}/captures");
  }

}
