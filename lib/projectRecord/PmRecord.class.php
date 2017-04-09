<?php

abstract class PmRecord extends ArrayAccesseble {

  /**
   * @return string
   */
  abstract protected function _getVhostFolder();

  /**
   * @return string
   */
  abstract protected function getVhostRecord();

  /**
   * @return void
   */
  abstract protected function saveRecord();

  protected $config;

  /**
   * @param array $record
   * @return PmRecord
   */
  static function get(array $record) {
    $class ='PmRecord'.ucfirst(Misc::camelCase($record['kind']));
    return new $class($record);
  }

  function __construct(array $record) {
    $this->config = O::get('PmLocalServerConfig');
    $this->r = $record;
  }

  /**
   * Receives records array from file
   *
   * @return array
   */
  abstract function getRecords();

  protected function getVhostFile() {
    return $this->getVhostFolder().'/'.$this->r['name'];
  }

  protected function getVhostFolder() {
    return Dir::make($this->_getVhostFolder());
  }


  function save() {
    $this->saveRecord();
    $this->saveVhost();
  }

  protected function saveVhost() {
    file_put_contents($this->getVhostFile(), $this->getVhostRecord());
  }

  protected function renderVhostAlias($location, $alias) {
    return "
  location $location {
    alias  $alias;
  }
";
  }

  protected function saveRecordFilterKeys() {
    return false;
  }


}