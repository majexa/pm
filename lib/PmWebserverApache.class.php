<?php

class PmWebserverApache extends PmWebserverAbstract {

  protected function renderVhostAlias($location, $alias) {
    return "
  Alias $location \"$alias\"";
  }

}