<?php

function sys2($cmd, $output = false) {
  if ($output and !getConstant('OUTPUT_DISABLE')) output('Cmd: '.$cmd, $output);
  exec($cmd, $output, $exitCode);
  if ($exitCode) throw new Exception($output);
  if ($output and !getConstant('OUTPUT_DISABLE') and $r) output("Cmd output: $r", $output);
  return $output;
}

class PmDnsManagerDevLinux extends PmDnsManagerDevWin {
  
  protected $configFile = '/etc/hosts';

  protected function save(array $items) {
    $file = $this->_save($items);
    sys2("sudo cat $file > {$this->configFile}");
  }
  
}