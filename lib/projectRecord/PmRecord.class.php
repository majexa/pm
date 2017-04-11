<?php

//static function renderVhostRecord($vhostTttt, array $d) {
//  if (empty($d['aliases'])) $d['aliases'] = '';
//  else $d['aliases'] = ' '.$d['aliases'];
//  return preg_replace('/^[ \t]*[\r\n]+/m', '', St::tttt($vhostTttt, $d));
//}


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
  abstract function saveRecord();

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

  function isWritable() {
    return false;
  }

  /**
   * Receives records array from file
   *
   * @return array
   */
  abstract function getRecords();

  protected function getVhostFile() {
    return $this->getVhostFolder().'/'.$this->r['name'].'.conf';
  }

  function getVhostFolder() {
    return Dir::make($this->_getVhostFolder());
  }

  function save() {
    $this->saveRecord();
    $this->saveVhost();
  }

  function saveVhost() {
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

  protected function renderVhostRecord($vhostTttt, $data) {
    if (empty($data['aliases'])) {
      $data['aliases'] = '';
    }
    else {
      $data['aliases'] = ' '.$data['aliases'];
    }
    $str = St::tttt($vhostTttt, $data, false);
    $str = preg_replace('/^\s*\n/m', '', $str);
    return $str;
  }

}