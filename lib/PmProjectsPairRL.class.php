<?php

/**
 * Remote to local project actions
 */
class PmProjectsPairRL extends PmProjectsPairAbstract {

  function a_copy() {
    $project = $this->getLocalProj();
    $project->importFsFromLocal($this->getRemoteProj()->localDownloadFs());
    $project->importDbFromLocal($this->getRemoteProj()->localDownloadDb());
  }

}