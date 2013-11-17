<?php

class PmWebserverAbstract {

  /**
   * @var PmLocalServerConfig
   */
  protected $config;

  function __construct() {
    $this->config = new PmLocalServerConfig;
    // todo: найти где заменяется end на пустоту и вставить его после

  }

  function restart() {
    $k = $this->config['os'] == 'win' ? ' -k' : '';
    Arr::checkEmpty($this->config->r, 'webserverP');
    PmCore::cmdSuper("{$this->config->r['webserverP']}$k restart");
  }

  protected function getFile($domain) {
    if (in_array($domain, array_keys(PmCore::getSystemWebFolders()))) {
      return Dir::make($this->config->r['webserverConfigFolder']).'/'.$domain;
    }
    return Dir::make($this->config->r['webserverProjectsConfigFolder']).'/'.$domain;
  }

  function saveVhost(array $v) {
    Arr::checkEmpty($v, 'name');
    if (isset($v['type'])) $v = array_merge(PmCore::config('types')[$v['type']], $v);
    file_put_contents($this->getFile($v['name']), $this->getVhostRecord($v));
    return $this;
  }

  function regen(array $records) {
    Dir::clear($this->config->r['webserverConfigFolder']);
    Dir::clear($this->config->r['webserverProjectsConfigFolder']);
    foreach ($records as $v) $this->saveVhost($v);
    return $this;
  }

  function getVhostRecord(array $v) {
    if (!isset($v['aliases'])) $v['aliases'] = [];
    if (!isset(PmCore::getSystemWebFolders()[$v['name']])) return $this->getProjectVhostRecord($v);
    else {
      return $this->getSystemVhostRecord($v['domain'], $v['name']);
    }
  }

  protected function getSystemVhostRecord($domain, $name) {
    if (isset($this->config[$name.'VhostTttt'])) {
      $tplName = $name.'VhostTttt';
      $record = array_merge(['domain' => $domain], $this->config->r);
    }
    else {
      $tplName = 'abstractVhostTttt';
      $record = array_merge($this->config->r, [
        'domain'  => $domain,
        'webroot' => PmCore::getSystemWebFolders()[$name],
        'end'     => ''
      ]);
    }
    $webRoot = PmCore::getSystemWebFolders()[$name];
    if (file_exists($webRoot.'/site') and file_exists($webRoot.'/u')) {
      $record['end'] = '
    location /i/ {
      access_log    off;
      expires       30d;
      add_header    Cache-Control public;
      root    /home/user/ngn-env/ngn;
    }
';
    }
    return self::renderVhostRecord($this->config[$tplName], $record);
  }

  /**
   * @param array [name, domain, aliases, innerTttt]
   * @return string
   */
  function getProjectVhostRecord(array $record) {
    $data = (new PmLocalProjectConfig($record['name']))->r;
    $data['domain'] = $record['domain'];
    if (!isset($record['aliases'])) $record['aliases'] = [];
    $record['aliases'][] = '*.'.$record['domain'];
    $data['aliases'] = implode(' ', $record['aliases']);
    $data['end'] = '';
    if (isset($record['vhostAliases'])) foreach ($record['vhostAliases'] as $k => $v) {
      $v = St::tttt($v, $data);
      $data['end'] .= $this->renderVhostAlias($k, $v);
    }
    return self::renderVhostRecord($data['vhostTttt'], $data);
  }

  protected function renderVhostAlias($location, $alias) {
  }

  function delete($name) {
    File::delete("{$this->config['webserverProjectsConfigFolder']}/$name");
    return $this;
  }

  static function renderVhostRecord($vhostTttt, array $d) {
    if (empty($d['aliases'])) $d['aliases'] = '';
    else $d['aliases'] = ' '.$d['aliases'];
    return preg_replace('/^[ \t]*[\r\n]+/m', '', St::tttt($vhostTttt, $d));
  }

}