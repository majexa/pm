<?php

class PmProjectItems extends ItemsIterateAbstract {

  function getItem($name) {
    return O::get('PmRecordsExisting')->getRecord($name)->r;
  }

  function getItems() {
    $records = array_filter(O::get('PmRecordsExisting')->getArray(), function($v) {
      return $v['kind'] !== 'system';
    });
    $sortKeys = array_flip(Arr::get((new PmProjectFields)->fields, 'name'));
    foreach ($sortKeys as &$key) $key = '';
    foreach ($records as &$record) $record = array_replace($sortKeys, $record);
    return $records;
  }

  function create(array $data) {
    throw new Exception('not implemented yet');
  }

  function update($id, array $data) {
    throw new Exception('not implemented yet');
  }

  function delete($id) {
    throw new Exception('not implemented yet');
  }

}