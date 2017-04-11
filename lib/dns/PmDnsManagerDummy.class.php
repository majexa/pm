<?php

class PmDnsManagerDummy extends PmDnsManagerAbstract {

  function create($domain) {
  }

  function delete($domain) {
  }

  protected function getItems() {
  }
  
  protected function save(array $items) {
  }
  
  function rename($oldDomain, $newDomain) {
  }

}