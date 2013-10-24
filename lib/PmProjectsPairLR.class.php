<?php

/**
 * Local to remote project actions
 */
class PmProjectsPairLR extends PmProjectsPairAbstract {

  function a_copy() {
    $this->a_copyFs();
    $this->getRemoteProj()->importDbFromLocal($this->getLocalProj()->localDownloadDb());
  }

  function a_copyFs() {
    $this->getRemoteProj()->importFsFromLocal($this->getLocalProj()->localDownloadFs());
  }

  /**
   * @options strName, ids
   */
  function a_copyDdItems() {
    file_put_contents(PmManager::$tempPath.'/db.sql', DdCore::exportItems($this->options['strName'], explode(',', $this->options['ids'])));
  }

}