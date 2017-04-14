<?php

/**
 * @property string $name
 * @property string $domain
 */
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
   * @param array|integer $recordOrId
   * @return PmRecord
   * @throws Exception
   */
  static function factory($recordOrId) {
    if (is_integer($recordOrId)) {
      $record = O::get('PmRecordsExisting')[$recordOrId]->r;
    } elseif (is_array($recordOrId)) {
      $record = $recordOrId;
    } else {
      throw new Exception('Unsupported type "' . gettype($recordOrId) . '" of $recordOrId');
    }
    $class = 'PmRecord'.ucfirst(Misc::camelCase($record['kind']));
    return new $class($record);
  }

  static function model($kind) {
    return self::factory([
      'kind' => $kind,
      'name' => 'dummy'
    ]);
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

  function deleteVhost() {
    File::delete($this->getVhostFile());
  }

}