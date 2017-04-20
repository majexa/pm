<?php

class TestPmRecord extends NgnTestCase {

  function test() {
    $recordsWritable = new PmRecordsWritable;
    $data = [
      'name' => 'test',
      'domain' => 'test.local',
      'kind' => 'project',
      'type' => 'common'
    ];
    $recordsWritable->delete('test', true);
    $recordsWritable->create($data);
    $recordsWritable->delete('test');

    $data = [
      'name' => 'test',
      'domain' => 'test.local',
      'kind' => 'php',
      'webroot' => '/home/user/ngn-env/pm'
    ];
    $recordsWritable->create($data);
    //$recordsWritable->delete('test');
  }

}