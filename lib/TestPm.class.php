<?php

class TestPm extends NgnTestCase {

  function test() {
    $this->cmd('pm localServer deleteProject test');
    $this->cmd('pm localServer deleteProject test2');
    $this->cmd('pm localServer createProject test default common');
    $pm = new PmManager('', ['disableRun' => true]);
    $project = new PmLocalProject([
      'disableRun'   => true,
      'name'         => 'test',
      'newDomain'    => 'test3',
      'newName'      => 'test3',
      'copyDomain'    => 'test2',
      'copyName'      => 'test2',
      'command'      => 'cc',
      'quietly'      => '1',
      'configKey'    => 'more',
      'configName'   => 'sample',
      'configValue'  => 'sample',
      'configSubKey' => 'sample'
    ]);
    foreach ($pm->getMethods('PmLocalProject') as $v) {
      output3($v['method']);
      $project->{'a_'.$v['method']}();
    }
    $this->cmd('pm localProject delete test2');
    $this->cmd('pm localProject delete test3');
  }

}