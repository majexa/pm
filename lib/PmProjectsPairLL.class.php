<?php

class PmProjectsPairLL extends Options2 {

  static $requiredOptions = [
    'domain1', 'domain2'
  ];

  /**
   * @var PmLocalProject
   */
  protected $lp1, $lp2;

  protected function init() {
    $this->lp1 = new PmLocalProject($this->options['domain1']);
    PmLocalProjectCore::createEmpty($this->options['domain2']);
    $this->lp2 = new PmLocalProject($this->options['domain2']);
  }

  function a_copy() {
    sys("rm -rf {$this->lp2['webroot']}");
    mkdir($this->lp2['webroot']);
    sys("cp -r {$this->lp1['webroot']}/* {$this->lp2['webroot']}");
    $file = TEMP_PATH.'/'.Misc::randString().".sql";
    $this->lp2->dbQuery("DROP DATABASE IF EXISTS {$this->lp2['dbName']}");
    $this->lp2->dbQuery("CREATE DATABASE {$this->lp2['dbName']}");
    sys("mysqldump ".$this->lp1->dbParams()." {$this->lp1['dbName']} > $file");
    sys("mysql ".$this->lp2->dbParams()." {$this->lp2['dbName']} < $file");
    unlink($file);
    $this->lp2->updateConfig();
    PmWebserver::get()->restart();
    Url::touch('http://'.$this->lp2['domain'].'/?cc=1');
  }

}