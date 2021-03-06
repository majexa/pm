<?php

/**
 * @method PmRecordWritable static model()
 */
abstract class PmRecordWritable extends PmRecord {

  /**
   * Returns file witch records array returns
   *
   * @return string
   */
  abstract protected function getRecordsFile();

  function getRecords() {
    if (!file_exists($this->getRecordsFile())) return [];
    return require $this->getRecordsFile();
  }

  function saveRecord() {
    Arr::checkEmpty($this->r, 'domain');
    $record = $this->r;
    if (($filterKeys = $this->saveRecordFilterKeys()) !== false) {
      $record = Arr::filterByKeys($this->r, $filterKeys);
    }
    unset($record['kind']);
    $records = $this->getRecords();
    if (($index = Arr::getKeyByValue($records, 'domain', $record['domain'])) !== false) {
      $records[$index] = $record;
    }
    else $records[] = $record;
    $this->saveRecords($records);
  }

  function saveRecords(array $records) {
    FileVar::updateVar($this->getRecordsFile(), $records);
  }

  function isWritable() {
    return true;
  }

}