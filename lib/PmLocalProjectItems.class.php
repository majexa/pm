<?php

class PmLocalProjectItems implements UpdatableItems {

  function getItem($id) {
    return Arr::getValueByKey($this->getRecords(), 'id', $id);
  }

  function getItemF($id) {
    return $this->getItem($id);
  }

  function getItemByField($k, $v) {
    return Arr::getValueByKey($this->getRecords(), $k, $v);
  }

  protected function getUsers($db, $p, $name) {
    if (($ids = $p->getVar('admins')) === false) return [];
    return $db->select('SELECT id, login, email FROM users WHERE id IN (?a)', $ids);
  }

  protected function getRecords() {
    $records = (new PmLocalProjectRecords())->getRecords();
    $n = 1;
    foreach ($records as &$v) {
      $v['id'] = $n;
      $n++;
    }
    return $records;
  }
  
  function getItems() {
    $r = [];//
    $startTime = time();
    foreach ($this->getRecords() as $v) {
      $p = new PmLocalProject($v['domain']);
      $v['title'] = $p->config->getConstant('site', 'SITE_TITLE');
      if (Db::dbExists($p->config->getConstant('database', 'DB_NAME'))) {
        $db = new Db($p['dbUser'], $p['dbPass'], $p['dbHost'], $p->config->getConstant('database', 'DB_NAME'));
        $v['admins'] = $this->getUsers($db, $p, 'admins');
        $v['gods'] = $this->getUsers($db, $p, 'gods');
        $v['size']['db'] = Db::getSize($db);
      } else $v['size']['db'] = 0;
      // Считаем размер папки, только если скрипт выполняется меньше 10 секунд
      if (time() - $startTime < 10) $v['size']['files'] = Dir::getSize($p['webroot'], 60*60*rand(10, 30));
      else $v['size']['files'] = 0;
      $v['size']['total'] = $v['size']['files'] + $v['size']['db'];
      $v['backups'] = SiteBackup::getBackups($v['domain']);
      if (file_exists($p['webroot'])) $v['timeCreate'] = filectime($p['webroot']);
      $r[] = $v;
    }
    return $r;
  }
  
  function create(array $data) {
    return count($this->getItems());
  }

  function event($name, $id) {}
  function update($id, array $data) {}
  function updateField($id, $k, $v) {}
  function getItemNonFormat($id) {}
  function delete($id) {}
  
}
