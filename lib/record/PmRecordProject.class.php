<?php

class PmRecordProject extends PmRecordWritable {

  protected function _getVhostFolder() {
    return $this->config['configPath'].'/nginx/project';
  }

  protected function getVhostRecord() {
    $record = $this->r;
    // getting initial data from project config
    $data = (new PmProjectConfig($record['name']))->r;
    $data['domain'] = $record['domain'];
    $data['name'] = $record['name'];
    if (!empty($this->r['type'])) {
      // add type data
      $typeData = O::get('PmProjectType', $this->r['type']);
      if ($typeData['vhostAliases']) {
        $data['vhostAliases'] = $typeData['vhostAliases'];
      }
    }
    // vhost aliases
    if (!isset($record['aliases'])) $record['aliases'] = [];
    $record['aliases'][] = '*.'.$record['domain'];
    $data['aliases'] = implode(' ', $record['aliases']);
    // add extra record at the end
    if (isset($record['vhostEnd'])) $data['end'] .= $record['vhostEnd'];
    if (isset($record['vhostRootLocation'])) $data['rootLocation'] = $record['vhostRootLocation'];
    else $data['rootLocation'] = '';
    // render with project settings
    return $this->renderVhostRecord($data['vhostTttt'], $data);
  }

  protected function getRecordsFile() {
    return $this->config['projectRecordsFile'];
  }

  protected function saveRecordFilterKeys() {
    return ['name', 'domain', 'type', 'aliases'];
  }

}