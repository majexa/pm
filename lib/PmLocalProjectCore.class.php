<?php

class PmLocalProjectCore {

  static function create(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    output2("Creating {$v['name']} project");
    if (!Misc::validName($v['name'])) throw new Exception("Name is not valid");
    if ($record = (new PmRecordsExisting)->getRecord($v['name'])) {
      throw new Exception("Project record '{$v['name']}' already exists");
    }
    PmRecord::factory($v)->save();
    if ($v['kind'] === 'project') {
      $config = new PmLocalProjectConfig($v['name']);
      (new PmLocalProjectFs($config))->prepareAndCopyToWebroot();
      PmDnsManager::get()->create($v['domain']);
      PmWebserver::get()->restart();
      $project = new PmLocalProject($v);
      if (!empty($project['type']) and empty($project['noDb'])) $project->importDummyDb();
      sys("pm localProject updateIndex {$v['name']}");
      sys("pm localProject updatePatchIds {$v['name']}");
      if (isset($config['afterCmdTttt'])) foreach ($config['afterCmdTttt'] as $cmd) sys($cmd, true);
      return $config['name'];
    } else {
      return $v['name'];
    }
  }

  static function createEmpty(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    if ((new PmLocalProjectRecords())->getRecord($v['domain'])) throw new Exception("Project '{$v['domain']}' already exists");
    (new PmLocalProjectRecords)->saveRecord($v);
    Dir::make((new PmLocalProjectConfig($v['name']))['webroot']);
    PmDnsManager::get()->create($v['domain']);
    PmWebserver::get()->saveVhost($v)->restart();
  }

}