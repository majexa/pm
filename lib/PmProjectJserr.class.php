<?php

class PmProjectJserr {

  protected $config;

  function __construct(PmLocalProjectConfig $config) {
    $this->config = $config;
    $this->init();
  }

  protected function init() {
    $dir = PM_PATH;
    file_put_contents("$dir/data/jserr", '');
    Cli::shell("phantomjs $dir/lib/jserr.js $dir/data/jserr {$this->config['domain']}/{$this->options['url']}");
    $lines = file("$dir/data/jserr");
    foreach ($lines as $line) {
      $this->renderError(json_decode($line, true));
    }
  }

  protected function renderError(array $err) {
    $max = 0;
    $maxI = 0;
    print $err[0];
    foreach ($err[1] as $i => $v) {
      if ($v['line'] > $max) {
        $maxI = $i;
        $max = $v['line'];
      }
    }
    $v = $err[1][$maxI];
    $v['file'] = Misc::removePrefix('http://'.$this->config['domain'], $v['file']);
    $v['file'] = preg_replace('/(.*)\?\d+/', '$1', $v['file']);
    $lines = file($this->config['webroot'].$v['file']);
    print ' ['.O::get('CliColors')->getColoredString($v['line'], 'red')."]\n";
    for ($i = $v['line'] - 6; $i < $v['line']; $i++) {
      print O::get('CliColors')->getColoredString($lines[$i], 'darkGray');
    }
    print "\n--\n";
  }

}