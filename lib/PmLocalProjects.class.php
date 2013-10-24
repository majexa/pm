<?php

class PmLocalProjects extends Options2 {

  static $set = true;
  protected $records;

  function init() {
    $this->records = (new PmLocalProjectRecords)->getRecords();
  }
  
  function action($action) {
    foreach ($this->records as $v) {
      $this->options['name'] = $v['name'];
      (new PmLocalProject($this->options))->{'a_'.$action}();
    }
  }

}
