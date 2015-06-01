<?php

/**
 * Remote to local project actions
 */
class PmProjectRL extends PmProjectSyncAbstract {

  /**
   * Копирует проект с удаленного сервера на локальный
   */
  function a_copy() {
    $this->getLocalProject()->importFs($this->getRemoteProject()->downloadFs());
    $this->getLocalProject()->importDb($this->getRemoteProject()->downloadDb());
  }

  function a_copyFs() {
    $this->getLocalProject()->importFs($this->getRemoteProject()->downloadFs());
  }

  function a_copyUpload() {
    $this->getRemoteProject()->downloadFs();
    //$this->getLocalProject()->importUpload();
  }

  function a_copyDb() {
    $this->getLocalProject()->importDb($this->getRemoteProject()->downloadDb());
  }

}