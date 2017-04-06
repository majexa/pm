<?php

class PmProjectItems extends ItemsIterateAbstract {

  function getItem($name) {
    return (new PmLocalProjectRecords)->getRecord($name);
  }

  function getItems() {
    return (new PmLocalProjectRecords)->getRecords();
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