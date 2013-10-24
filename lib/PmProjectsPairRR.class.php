<?php

/**
 * Remote & romote projects pair
 */
class PmProjectsPairRR extends Options2 {

  static $requiredOptions = [
    'server1', 'server2', 'domain'
  ];

  /**
   * @var PmRemoteProject
   */
  protected $oRP;
  /**
   * @var PmRemoteProject
   */
  protected $oRP2;
  
  protected function init() {
    $this->oRP = new PmRemoteProject(
      $this->options['server2'],
      $this->options['domain']
    );
    $this->oRP2 = new PmRemoteProject(
      $this->options['server1'],
      $this->options['domain']
    );
  }
  
  function a_copy() {
    $this->a_copyFs();
    $this->a_copyDb();
  }
  
  function a_copyFs() {
    $this->oRP->importFs($this->oRP->downloadFs($this->oRP2));
  }
  
  function a_copyDb() {
    $this->oRP->importDb($this->oRP->downloadDb($this->oRP2));
  }

}