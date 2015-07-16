<?php

/**
 * Remote to local project actions
 */
class PmProjectRL extends PmProjectSyncAbstract {

  /**
   * �������� ������ � ���������� ������� �� ���������
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
    $dumpFile = $this->getRemoteProject()->downloadDb();
    $this->getLocalProject()->importDb($dumpFile);
  }

}