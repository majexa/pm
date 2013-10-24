<?php

class CtrlCommonPm extends CtrlCp {
  
  protected function init() {
    $this->setModuleTitle('Управление проектами');
    $this->setTopLinks([
      [
        'title' => 'Проекты',
        'link' => $this->tt->getPath(),
        'class' => 'list'
      ],
      [
        'title' => 'Создать проект',
        'link' => $this->tt->getPath(2).'?a=new',
        'class' => 'add'
      ]
    ]);
  }
  
  function action_default() {
    $this->setPageTitle('Проекты');
    $this->d['items'] = (new PmLocalProjectItems())->getItems();
    $r =& $this->d['items'];
    $r = Arr::assoc($r, 'domain');
    foreach ($r as $k => $v) {
      $r[$k]['testCopyInProgress'] = NgnCl::slowCmdIsRunning("copyProjectToTest{$v['id']}");
      if (Misc::hasPrefix('test.', $k) and isset($r[Misc::removePrefix('test.', $k)])) {
        $v['test'] = true;
        $r[Misc::removePrefix('test.', $k)]['testProject'] = $v;
        unset($r[$k]);
      }

    }
  }
  
  function action_json_new() {
    $im = new PmLocalItemsManager();
    if ($im->requestCreate()) return;
    return $im->form;
  }
  
  function action_json_edit() {
    $im = new PmLocalItemsManager();
    if ($im->requestUpdate($this->req->r['id'])) return;
    return $im->form;
  }
  
  function action_ajax_delete() {
    O::get('PmLocalItemsManager')->delete($this->req->rq('id'));
  }

  function action_ajax_copyToTest() {
    $project = (new PmLocalItemsManager())->items->getItem($this->req->rq('id'));
    Misc::checkEmpty($project);
    NgnCl::slowCmd(
      'copyProjectToTest'.$this->req->rq('id'),
      "php ".NGN_ENV_PATH."/pm/pm.php pairLL {$project['domain']} test.{$project['domain']} copy"
    );
  }

  protected function getLastId($project) {
    $path = (new PmLocalServerConfig())['projectsPath'];
    $id = 0;
    foreach (Dir::_getDirs($path, false, 'v\d+*') as $v) {
      $id = preg_replace('/.*\/v(\d+)\..*/', '$1', $v);
    }
    return $id;
  }

  function action_ajax_copyToNextVersion() {
    $project = (new PmLocalItemsManager())->items->getItem($this->req->rq('id'));
    Misc::checkEmpty($project);
    $nextVersion = $this->getLastId($project) + 1;
    NgnCl::slowCmd(
      'copyToNextVersion'.$this->req->rq('id'),
      "php ".NGN_ENV_PATH."/pm/pm.php pairLL {$project['domain']} v$nextVersion.{$project['domain']} copy"
    );
  }

}