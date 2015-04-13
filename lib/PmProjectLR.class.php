<?php

/**
 * Local to remote project actions
 */
class PmProjectLR extends PmProjectSyncAbstract {

  function a_copy() {
    $this->a_copyFs();
    $this->a_copyDb();
  }

  function a_copyDb() {
    $this->getRemoteProject()->importDbFromLocal($this->getLocalProject()->localDownloadDb());
  }

  function a_copyFs() {
    $this->getRemoteProject()->importFsFromLocal($this->getLocalProject()->localDownloadFs());
  }

  /**
   * @options strName, ids
   */
  function a_copyDdItems() {
    file_put_contents(PmManager::$tempPath.'/db.sql', DdCore::exportItems($this->options['strName'], explode(',', $this->options['ids'])));
  }

}