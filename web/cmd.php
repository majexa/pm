<?php

require __DIR__.'/init.php';
require dirname(dirname(__DIR__)).'/ngn/init/cli-standalone.php';
Ngn::$basePaths[] = __DIR__.'/site';

if (strstr($_SERVER['argv'][1], '(')) { // eval
  $cmd = trim($_SERVER['argv'][1]);
  if ($cmd[strlen($cmd)-1] != ';') $cmd = "$cmd;";
  eval($cmd);
  return;
}

if (isset($_SERVER['argv'][1])) {
  $found = false;
  foreach (Ngn::$basePaths as $path) {
    $file = "$path/cmd/{$_SERVER['argv'][1]}.php";
    if (file_exists($file)) {
      require $file;
      $found = true;
      break;
    }
  }
}
if (!$found) throw new NotFoundException($_SERVER['argv'][1]);

