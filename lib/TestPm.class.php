<?php

class TestPm extends NgnTestCase {

  function test() {
    $this->assertFalse(false);
//    $this->cmd('pm localServer deleteProject test');
//    $this->cmd('pm localServer deleteProject test2');
//    $this->cmd('pm localServer createProject test default common');
//    $pm = new PmManager('', ['disableRun' => true]);
//    $project = new PmLocalProject([
//      'disableRun'   => true,
//      'name'         => 'test',
//      'newDomain'    => 'test3',
//      'newName'      => 'test3',
//      'copyDomain'    => 'test2',
//      'copyName'      => 'test2',
//      'command'      => 'cc',
//      'quietly'      => '1',
//      'configKey'    => 'more',
//      'configName'   => 'sample',
//      'configValue'  => 'sample',
//      'configSubKey' => 'sample'
//    ]);
//    foreach ($pm->getMethods('PmLocalProject') as $v) {
//      output3($v['method']);
//      $project->{'a_'.$v['method']}();
//    }
//    $this->cmd('pm localProject delete test2');
//    $this->cmd('pm localProject delete test3');
  }

  function testAllHostsAvailability() {
    $curl = new Curl;
    foreach ((new PmLocalServer)->getRecords() as $record) {
      $curl->setopt(CURLOPT_URL, 'http://'.$record['domain']);
      $pageContents = $curl->exec();
      if (curl_getinfo($curl->fSocket, CURLINFO_HTTP_CODE) == 404) {
        $pageContents = str_ireplace(["<hr />","<hr>","<hr/>"], "-------\n", $pageContents);
        $pageContents = str_ireplace(["<br />","<br>","<br/>"], "\n", $pageContents);
        $pageContents = strip_tags($pageContents);
        $this->assertTrue(false, '404 on '.$record['domain'].". Page contents:\n".$pageContents);
        break;
      }
    }
    $caption = '';
    foreach ((new AllErrors)->get() as $v) {
      $caption .= $v['body']."\n".$v['trace']. //
        (isset($v['params']['entryCmd']) ? "Entry cmd: ".$v['params']['entryCmd'] : '')."\n=======\n";
    }
    $this->assertTrue($caption === '', $caption);
  }

}
