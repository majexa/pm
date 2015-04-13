<?php

class Tar {

  static function create($fileOrFolder) {
    $tarBaseFolder = dirname($fileOrFolder);
    $file = basename($fileOrFolder);
    //print "tar -czf $file.tgz $fileOrFolder -C $tarBaseFolder\n";
    print "cd $tarBaseFolder; tar -czf $file.tgz $file\n";
    return shell_exec("cd $tarBaseFolder; tar -czf $file.tgz $file");
  }

}