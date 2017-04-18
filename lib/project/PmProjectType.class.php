<?php

class PmProjectType extends ArrayAccesseble {

  protected $multipleParams = ['vhostAliases', 'afterCmdTttt'];

  function __construct($type) {
    $this->r['type'] = $type;
    $types = self::types();
    if (!isset($types[$this->r['type']])) throw new Exception("Type '{$this->r['type']}' does not exists");
    $type = $types[$this->r['type']];
    foreach ($this->multipleParams as $p) if (isset($type[$p])) $type[$p] = (array)$type[$p]; // normalize multiples to arrays
    if (isset($type['extends'])) {
      $extendingType = $types[$type['extends']];
      foreach ($this->multipleParams as $p) {
        if (isset($extendingType[$p])) {
          $extendingType[$p] = (array)$extendingType[$p];
          if (!isset($type[$p])) $type[$p] = [];
          $type[$p] = array_merge($extendingType[$p], $type[$p]);
        }
      }
      foreach (Arr::filterByExceptKeys($extendingType, $this->multipleParams) as $k => $v) {
        $type[$k] = $v;
      }
    }
    $this->r = array_merge($this->r, $type);
  }

  /**
   * @return String
   */
  function __toString() {
    return $this->r['type'];
  }

  static function types() {
    $types = require PM_PATH."/config/types.php";
    $customTypesFile = PM_PATH.'/config/customTypes.php';
    if (file_exists($customTypesFile)) {
      $types = array_merge($types, require $customTypesFile);
    }
    foreach (glob(NGN_ENV_PATH.'/*', GLOB_ONLYDIR) as $folder) {
      if (file_exists($folder.'/pmConfig.php')) {
        $r[basename($folder)] = require $folder.'/pmConfig.php';
        $types = array_merge($types, $r);
      }
    }
    return $types;
  }

}