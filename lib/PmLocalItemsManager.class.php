<?php

PmCore::check();

class PmLocalItemsManager extends ItemsManager {

  /**
   * @var DbItems
   */
  public $items;
  
  function __construct(array $options = []) {
    parent::__construct(
      new PmLocalProjectItems(),
      new Form(new Fields([
        [
          'title' => 'Домен',
          'name' => 'domain',
          'type' => 'domain',
          'required' => true
        ],
        [
          'title' => 'Алиасы',
          'name' => 'aliases',
          'type' => 'fieldList',
          'filterEmpties' => false,
          'fieldsType' => 'domain',
          'headerToggle' => true,
          'addTitle' => 'Добавить алиас'
        ]
      ])),
      $options
    );
  }

  protected function getNgnSubFolders() {
    return Arr::toOptions(Dir::dirs(dirname(NGN_PATH)));
  }
  
  protected function checkExistence() {
    if ($this->items->getItemByField('domain', $this->data['domain']))
      throw new FormError('domain', "Проект с доменом «{$this->data['domain']}» уже существует");
  }

  protected function beforeCreate() {
    $this->checkExistence();
    PmLocalProjectCore::create($this->data);
    if (!empty($this->data['aliases']))
      (new PmLocalProject($this->data['domain']))->updateAliases($this->data['aliases']);
    PmWebserver::get()->restart();
  }

  protected function afterUpdate() {
    $oPL = O::get('PmLocalProject', $this->beforeUpdateData['domain']);
    $oPL->updateAliases(empty($this->data['aliases']) ? null : $this->data['aliases']);
    if ($this->form->getElement('domain')->valueChanged)
      $oPL->_rename($this->data['domain']);
    PmWebserver::get()->restart();
    Url::touch("http://{$this->data['domain']}/?cc=1");
  }

  protected function beforeDelete() {
    O::get('PmLocalProject', $this->data['domain'])->a_delete();
  }
  
  function deleteByDomain($domain) {
    if (($item = $this->items->getItemByField('domain', $domain)) == false) return;
    $this->delete($item['id']);
  }

  function getAttacheFolder() {
    return false;
  }
  
}
