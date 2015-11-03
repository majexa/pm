<?php

/**
 * Remote to local project actions
 */
class PmProjectRL extends PmProjectSyncAbstract {

  /**
   * Copy project
   */
  function a_copy() {
    $recordCopied = $this->copyRecord();
    $this->copyDb();
    $this->copyFs();
    if ($recordCopied) (new PmLocalServer)->updateHosts();
    output('done');
  }

  /**
   * Copy project files
   */
  function a_copyFs() {
//    if (!(new GitFolder($this->getLocalProject()->config['webroot']))->isClean()) {
//      output('project git is not clean');
//      return;
//    }
    $this->copyFs();
    output('done');
  }

  /**
   * Copy project database
   */
  function a_copyDb() {
    $this->copyDb();
    output('done');
  }

  function copyRecord() {
    $records = new PmLocalProjectRecords;
    if ($records->getRecord($this->options['projectName'])) return false;
    if (!($domain = Cli::prompt('Set domain for project "'.$this->options['projectName'].'"'))) return false;
    $record = $this->getRemoteProject()->getServer()->remoteSshCommand("pm localProject record {$this->options['projectName']}", false);
    $record = eval('?>'.$record);
    $record['domain'] = $domain;
    (new PmLocalProjectRecords)->saveRecord($record);
    return true;
  }

  protected function copyFs() {
    $tempWebroot = $this->getRemoteProject()->downloadFs();
    Dir::copy($tempWebroot, $this->getLocalProject()->config['webroot'], false);
    $this->getLocalProject()->chmod();
  }

  protected function copyDb() {
    if (!$this->getRemoteProject()->getServer()->remoteSshCommand("pm localProject dbExists {$this->options['projectName']}")) {
      output('project db not exists');
      return;
    }
    $dumpFile = $this->getRemoteProject()->downloadDb();
    $this->getLocalProject()->importDb($dumpFile);
  }

}