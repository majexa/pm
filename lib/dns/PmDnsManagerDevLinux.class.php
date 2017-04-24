<?php

function sys2($cmd, $output = false) {
  if ($output and !getConstant('OUTPUT_DISABLE')) output('Cmd: '.$cmd, $output);
  $r = exec($cmd, $a, $exitCode);
  if ($exitCode) exit($exitCode);
  if ($output and !getConstant('OUTPUT_DISABLE') and $r) output("Cmd output: $r", $output);
  return $output;
}

class PmDnsManagerDevLinux extends PmDnsManagerDevWin {
  
  protected $configFile = '/etc/hosts';

  protected function save() {
    $file = $this->_save();
    print `sudo cat $file > {$this->configFile}`;
    `echo $?`;
    sys2("sudo cat $file > {$this->configFile}");
  }
  
}