<?php

abstract class PmProjectsPairAbstract extends ArrayAccessebleOptions {

  protected $name, $remoteServerName;

  static $requiredOptions = ['remoteServerName', 'projectName'];

  function init() {
    $this->remoteServerName = $this->options['remoteServerName'];
    $this->name = $this->options['projectName'];
  }

  /**
   * @return PmLocalProject
   */
  protected function getLocalProj() {
    return O::get('PmLocalProject', ['name' => $this->name]);
  }

  /**
   * @return PmRemoteProject
   */
  protected function getRemoteProj() {
    return O::get('PmRemoteProjectDev', $this->remoteServerName, $this->domain);
  }

}