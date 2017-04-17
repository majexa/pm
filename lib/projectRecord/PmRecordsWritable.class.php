<?php

class PmRecordsWritable extends PmRecordsExisting {

  static $writableKinds = ['project', 'php'];

  function __construct() {
    parent::__construct();
    $this->r = array_filter($this->r, function(PmRecord $v) {
      return $v->isWritable();
    });
  }

  function storeRecords() {
    $grouped = [];
    foreach ($this->getArray() as $v) {
      $kind = $v['kind'];
      unset($v['kind']);
      $grouped[$kind][] = $v;
    }
    foreach (self::$writableKinds as $kind) {
      /* @var $writableRecordModel PmRecordWritable */
      $writableRecordModel = PmRecord::model($kind);
      $writableRecordModel->saveRecords(isset($grouped[$kind]) ? $grouped[$kind] : []);
    }
  }

  function delete($name, $silent = false) {
    $index = Arr::getKeyByValue($this->r,'name', $name);
    if ($index === false) {
      if ($silent) return;
      throw new NotFoundException('Record "' . $name . '"');
    }
    unset($this->r[$index]);
    $this->storeRecords();
    $this->regenVhosts();
  }

  function create(array $data) {
    if (Arr::getKeyByValue($this->r,'name', $data['name']) !== false) {
      throw new AlreadyExistsException;
    }
    $model = PmRecord::factory($data);
    $model->save();
    $this->r[] = $model;
    $this->regenVhosts();
  }

  function update($name, array $data) {
    if (isset($data['kind'])) unset($data['kind']);
    $index = Arr::getKeyByValue($this->r,'name', $name);
    $this->r[$index]->r = array_merge($this->r[$index]->r, $data);
  }

  function clearVhosts() {
    foreach (['project', 'php'] as $kind) {
      $recordModel = PmRecord::model($kind);
      Dir::clear($recordModel->getVhostFolder());
    }
  }

}