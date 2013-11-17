<?php

class PmLocalProjectCore {

  static function createRecordAndVhost(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    if ((new PmLocalProjectRecords())->getRecord($v['domain'])) throw new Exception("Project '{$v['domain']}' already exists");
    (new PmLocalProjectRecords)->saveRecord($v);
    PmDnsManager::get()->create($v['domain']);
    PmWebserver::get()->saveVhost($v);
  }

  static function create(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    if (!Misc::validName($v['name'])) throw new Exception("Name is not valid");
    if ((new PmLocalProjectRecords())->getRecord($v['domain'])) throw new Exception("Project '{$v['domain']}' already exists");
    (new PmLocalProjectRecords)->saveRecord($v);
    $config = new PmLocalProjectConfig($v['name']);
    (new PmLocalProjectFs($config))->prepareAndCopyToWebroot();
    PmDnsManager::get()->create($v['domain']);
    PmWebserver::get()->saveVhost($v)->restart();
    $project = new PmLocalProject($v);
    if (empty($project['noDb'])) $project->importDummyDb();
    sys("php {$config['ngnEnvPath']}/scripts/updateIndex.php");
    sys("php {$config['pmPath']}/pm.php localProject updatePatchIds {$v['name']}");
    if (isset($config['afterCmdTttt'])) sys($project['afterCmdTttt'], true);
    return $config['name'];
  }

  static function createEmpty($domain) {
    if ((new PmLocalProjectRecords())->getRecord($domain)) return false;
    (new PmLocalProjectRecords())->saveRecord(['domain' => $domain]);
    PmDnsManager::get()->create($domain);
    PmWebserver::get()->saveVhost(['domain' => $domain]);
    return true;
  }

}