<?php

class PmRecordProject extends PmRecordWritable {

  protected function _getVhostFolder() {
    return $this->config->r['webserverProjectsConfigFolder'];
  }

  protected function getVhostRecord() {
    $record = $this->r;
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
    $this->renderVhostRecord($data['vhostTttt'], $data);
  }

  protected function getRecordsFile() {
    return $this->config['projectRecordsFile'];
  }

  protected function saveRecordFilterKeys() {
    return ['name', 'domain', 'type', 'aliases'];
  }

}