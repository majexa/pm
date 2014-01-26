<?php

class PmWebserverNginx extends PmWebserverAbstract {

  function restart() {
    PmCore::cmdSuper("{$this->config->r['webserverP']} restart");
  }

  protected function renderVhostAlias($location, $alias) {
    return "
  location $location {
    alias  $alias;
  }
";
  }

}