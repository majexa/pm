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
   * Returns rendered value or false if not exists
   *
   * @param $name
   * @param $prop
   * @return bool|string
   */
  function render($name, $prop) {
    if (!isset($this->r[$prop])) return false;
    $config = O::get('PmProjectConfig', $name);
    $value = $this->r[$prop];
    if (is_array($value)) {
      foreach ($value as &$v) {
        $v = St::tttt($v, $config->r);
      }
    }
    return $value;
  }

  /**
   * @return String
   */
  function __toString() {
    return $this->r['type'];
  }

  static function types() {
    $pmRoot = dirname(dirname(__DIR__));
    $types = require "$pmRoot/config/types.php";
    $customTypesFile = "$pmRoot/config/customTypes.php";
    if (file_exists($customTypesFile)) {
      $types = array_merge($types, require $customTypesFile);
    }
    foreach (glob(NGN_ENV_PATH.'/*', GLOB_ONLYDIR) as $folder) {
      $file = "$folder/pmConfig.php";
      if (file_exists($file)) {
        $r[basename($folder)] = require $file;

        $types = array_merge($types, $r);
      }
    }
    return $types;
  }

}