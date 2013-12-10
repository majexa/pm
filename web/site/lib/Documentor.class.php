<?php

class Documentor {

  static function getFileDocBlock($file) {
    $docComments = array_filter(token_get_all(file_get_contents($file)), function ($entry) {
      return $entry[0] == T_DOC_COMMENT;
    });
    $fileDocComment = array_shift($docComments);
    if (!$fileDocComment) return false;
    $fileDocComment[1] = preg_replace('/\/\*+(.*)\*\//ms', '$1', $fileDocComment[1]);
    $fileDocComment[1] = preg_replace('/\s+\*\s+(.*)/m', "$1\n", $fileDocComment[1]);
    return trim($fileDocComment[1]);
  }

  static function getNgnFileDockBlock($file) {
    $c = self::getFileDocBlock($file);
    if (strstr($c, '@manual')) return trim(str_replace('@manual', '', $c));
    return false;
  }

  static function filesR($folder) {
    $r = [];
    foreach (glob("$folder/*") as $f) {
      if (is_dir($f)) {
        $r = array_merge($r, self::filesR($f));
        continue;
      }
      $r[] = $f;
    }
    return $r;
  }

  static function getAll() {
    $r = [];
    foreach (glob(NGN_ENV_PATH.'/ngn', GLOB_ONLYDIR) as $folder) {
      $package = basename($folder);
      foreach (self::filesR($folder) as $file) {
        if (($doc = self::getNgnFileDockBlock($file))) {
          if (!isset($r[$package])) $r[$package] = [];
          $r[$package][] = [
            'doc' => $doc,
            'file' => $file
          ];
        }
        //$n++; if ($n == 10) break;
      }
    }
    die2($r);
  }

}