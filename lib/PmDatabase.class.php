<?php

trait PmDatabase {

  protected function createDb($name) {
    Db::createDb($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $name);
  }

  protected function _importDummyDb($name) {
    $this->importSqlDump($this->config['ngnPath'].'/dummy.sql', $name);
  }

  function importSqlDump($sqlFile, $name) {
    $db = new Db($this->config['dbUser'], $this->config['dbPass'], $this->config['dbHost'], $name);
    output('Import DB "'.$sqlFile.'"');
    $db->query('SET NAMES utf8');
    $db->importFile($sqlFile);
  }

}