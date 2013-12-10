<?php

class Activity {

  static function files() {
    $files = [];
    foreach (Dir::getFilesR(dirname(NGN_ENV_PATH)) as $file) {
      if (strstr($file, '/data/')) continue;
      if (strstr($file, '/logs/')) continue;
      if (strstr($file, '/cache/')) continue;
      $files[] = $file;
    }
    return $files;
  }

}