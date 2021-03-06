<?php

class PmProjectCore {

  static function create(array $v) {
    Arr::checkEmpty($v, ['name', 'domain']);
    output2("Creating {$v['name']} project");
    if (!Misc::validName($v['name'])) throw new Exception("Name is not valid");
    if ($record = (new PmRecordsExisting)->getRecord($v['name'])) {
      throw new Exception("Project record '{$v['name']}' already exists");
    }
    $config = new PmProjectConfig($v['name']);
    $v['kind'] = 'project';
    $record = PmRecord::factory($v);
    $record->save();
    $v['kind'] = 'project';
    (new PmLocalProjectFs($config))->prepareAndCopyToWebroot();
    PmDnsManager::factory()->createAndSave($v['domain']);
    PmWebserver::get()->restart();
    $project = new PmLocalProject($v);
    if ($record['type'] and !$record['noDb']) $project->importDummyDb();
    sys("pm localProject updateIndex {$v['name']}");
    sys("pm localProject updatePatchIds {$v['name']}");


    $afterCmdTttt = O::get('PmProjectType', $v['type'])->render($v['name'], 'afterCmdTttt');
    if ($afterCmdTttt !== false) {
      foreach ($afterCmdTttt as $cmd) {
        sys($cmd, true);
      }
    }

    return $config['name'];
  }

  static function createEmpty(array $v) {
    throw new Exception('deprecated');
//    Arr::checkEmpty($v, ['name', 'domain']);
//    if ((new PmLocalProjectRecords())->getRecord($v['domain'])) throw new Exception("Project '{$v['domain']}' already exists");
//    (new PmLocalProjectRecords)->saveRecord($v);
//    Dir::make((new PmLocalProjectConfig($v['name']))['webroot']);
//    PmDnsManager::factory()->create($v['domain']);
//    PmWebserver::get()->saveVhost($v)->restart();
  }

}