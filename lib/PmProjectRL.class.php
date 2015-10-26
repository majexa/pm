<?php

/**
 * Remote to local project actions
 */
class PmProjectRL extends PmProjectSyncAbstract {

  /**
   * Copy project
   */
  function a_copy() {
    // create project if not exists

    $this->copyDb();
    $this->copyFs();
    output('done');
  }

  /**
   * Copy project files
   */
  function a_copyFs() {
    if (!(new GitFolder($this->getLocalProject()->config['webroot']))->isClean()) {
      output('project git is not clean');
      return;
    }
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
    print $this->getRemoteProject()->getServer()->remoteSshCommand("pm localProject record {$this->options['projectName']}");
  }

  protected function copyFs() {
    $tempWebroot = $this->getRemoteProject()->downloadFs();
    Dir::copy($tempWebroot, $this->getLocalProject()->config['webroot'], false);
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