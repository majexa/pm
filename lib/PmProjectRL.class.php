<?php

/**
 * Remote to local project actions
 */
class PmProjectRL extends PmProjectSyncAbstract {

  /**
   * Copy project
   */
  function a_copy() {
    $this->copyDb();
    $this->copyFs();
    output('done');
  }

  /**
   * Copy project files
   */
  function a_copyFs() {
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

  protected function copyFs() {
    if (!(new GitFolder($this->getLocalProject()->config['webroot']))->isClean()) {
      output('project git is not clean');
      return;
    }
    $tempWebroot = $this->getRemoteProject()->downloadFs();
    Dir::copy($tempWebroot, $this->getLocalProject()->config['webroot'], false);
  }

  protected function copyDb() {
    $dumpFile = $this->getRemoteProject()->downloadDb();
    $this->getLocalProject()->importDb($dumpFile);
  }

}