<?php

class PmRecordsProject extends PmRecordsWritable {

  function __construct() {
    $this->addRecords('project');
    $this->existingKinds[] = 'project';
  }

}