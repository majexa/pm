<?php

abstract class PmWebserverAbstract {

  /**
   * @var PmLocalServerConfig
   */
  protected $config;

  function __construct() {
    $this->config = new PmLocalServerConfig;
  }

  function restart() {
    $k = $this->config['os'] == 'win' ? ' -k' : '';
    Misc::checkEmpty($this->config->r['webserverP'], 'webserver path "webserverP" must be defined in server config');
    PmCore::cmdSuper("'{$this->config->r['webserverP']}'$k restart");
    //shell_exec("'{$this->config->r['webserverP']}'$k restart");
  }

  protected function getFile($domain) {
    if (in_array($domain, array_keys(PmCore::getSystemWebFolders()))) {
      return Dir::make($this->config->r['webserverSystemConfigFolder']).'/'.$domain;
    }
    return Dir::make($this->config->r['webserverProjectsConfigFolder']).'/'.$domain;
  }

  function saveVhost(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    $types = PmCore::types();
    if (isset($v['type'])) $v = array_merge(isset($types[$v['type']]) ? $types[$v['type']] : [], $v);
    file_put_contents($this->getFile($v['name']), $this->getVhostRecord($v));
    return $this;
  }

  function regen(array $records) {
    Dir::clear($this->config->r['webserverSystemConfigFolder']);
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
    $record['rootLocation'] = '';
    $record['end'] = <<<RECORD

  location /i/ {
    access_log    off;
    expires       30d;
    add_header    Cache-Control public;
    root    /home/user/ngn-env/ngn;
  }

RECORD;
    $r = self::renderVhostRecord($this->config[$tplName], $record);
    if ($name == 'default') $r = preg_replace('/(listen\s+)(\d+)(;)/', '$1$2 default_server$3', $r);
    return $r;
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
    if (isset($record['vhostEnd'])) $data['end'] .= $record['vhostEnd'];
    if (isset($record['vhostRootLocation'])) $data['rootLocation'] = $record['vhostRootLocation'];
    else $data['rootLocation'] = '';
    return self::renderVhostRecord($data['vhostTttt'], $data);
  }

  abstract protected function renderVhostAlias($location, $alias);

  function delete($name) {
    File::delete("{$this->config['webserverProjectsConfigFolder']}/$name");
    return $this;
  }

  static function renderVhostRecord($vhostTttt, array $d) {
    if (!isset($d['rootLocation'])) die2('!');

    if (empty($d['aliases'])) $d['aliases'] = '';
    else $d['aliases'] = ' '.$d['aliases'];
    return preg_replace('/^[ \t]*[\r\n]+/m', '', St::tttt($vhostTttt, $d));
  }

}