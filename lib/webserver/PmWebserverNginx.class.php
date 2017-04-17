<?php

class PmWebserverNginx extends PmWebserverAbstract {

  function restart() {
    PmCore::cmdSuper("{$this->config->r['webserverP']} reload");
  }

  protected function renderVhostAlias($location, $alias) {
    return "
  location $location {
    alias  $alias;
  }
";
  }

}