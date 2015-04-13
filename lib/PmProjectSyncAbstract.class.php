<?php

abstract class PmProjectSyncAbstract extends ArrayAccessebleOptions {

  protected $name, $remoteServerName;

  static $requiredOptions = ['remoteServerName', 'projectName'];

  /**
   * @return PmLocalProject
   */
  protected function getLocalProject() {
    return O::get('PmLocalProject', ['name' => $this->options['projectName']]);
  }

  /**
   * @return PmRemoteProject
   */
  protected function getRemoteProject() {
    return O::get('PmRemoteProject', $this->options['remoteServerName'], $this->options['projectName']);
  }

}