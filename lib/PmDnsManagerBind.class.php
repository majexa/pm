<?php

class PmDnsManagerBind extends PmDnsManagerAbstract {

  protected $server;

  function __construct() {
    $this->server = require '/home/user/ngn-env/config/server.php';
  }

  protected function cmd($code) {
    return Cli::runCode('root@'.$this->server['dnsMasterHost'], $code, 'NGN_ENV_PATH/dns-server/lib');
  }

  function create($domain) {
    if (preg_match('/.*\.(\w+.\w+.\w+)/', $domain)) {
      // Если уровень домена больше 3, отсекаем всё, что больше
      $domain = preg_replace('/.*\.(\w+.\w+.\w+)/', '$1', $domain);
    }
    $this->_create($domain);
    if (preg_match('/(\w+.\w+.\w+)/', $domain)) $this->_create('*.'.$domain);
  }

  protected function exists($domain) {
  }

  protected function _create($domain) {
    $this->cmd("(new DnsServer)->createZone('$domain', '{$this->server['host']}')");
  }

  function delete($domain) {
    if (preg_match('/.*\.(\w+.\w+.\w+)/', $domain)) {
      // Если уровень домена больше 3, необходимо проверить, есть ли ещё сабдомены в зоне домена 3-го уровня
    }
    $this->cmd("(new DnsServer)->deleteZone('$domain')");
  }

  protected function checkZone($domain) {
    $dig = sys("dig A $domain @ns1.majexa.ru");
    $d = str_replace('.', '\\.', $domain.'.');
    return preg_match('/;; ANSWER SECTION:\n'.$d.'\s+\d+\s+IN\s+A\s+'.$this->server['host'].'/m', $dig);
  }

  protected function getItems() {
    throw new Exception('not realized');
  }
  
  protected function save(array $items) {
    throw new Exception('not realized');
  }
  
  function rename($oldDomain, $newDomain) {
    throw new Exception('not realized');
  }

}