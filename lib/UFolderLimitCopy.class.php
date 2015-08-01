<?php

class UFolderLimitCopy {

  protected $uFolder, $destination;

  function __construct($uFolder, $destination) {
    $this->uFolder = $uFolder;
    $this->destination = $destination;
    Dir::make($this->destination);
    foreach (glob($uFolder.'/*') as $folder) {
      if (preg_match('/css|js|thumb|dd/', basename($folder))) continue;
      $this->folderRestricted($folder, 10);
    }
    $this->foldersRestricted(glob($uFolder.'/dd/*'), 5);
    $this->foldersRestricted(glob($uFolder.'/thumb/dd/*'), 5);
  }

  function folderRestricted($folder, $limit) {
    $relativePath = str_replace($this->uFolder, UPLOAD_DIR, $folder);
    Dir::make($this->destination.'/'.$relativePath);
    $this->foldersRestricted([$folder], $limit);
  }

  function foldersRestricted(array $folders, $limit) {
    foreach ($folders as $folder) {
      $items = glob($folder.'/*');
      usort($items, create_function('$a,$b', 'return filemtime($a) - filemtime($b);'));
      $items = array_slice($items, 0, $limit);
      $items = array_reverse($items);
      $this->copy($items);
    }
  }

  protected function copy(array $items) {
    foreach ($items as $f) {
      $relativePath = str_replace($this->uFolder, UPLOAD_DIR, $f);
      if (is_dir($f)) {
        Dir::copy($f, $this->destination.'/'.$relativePath);
      } else {
        copy($f, $this->destination.'/'.$relativePath);
      }
    }
  }

}
