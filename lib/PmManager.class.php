<?php

class PmManager {

  static $tempPath, $downloadPath;

  function __construct($argv) {
    if (!isset($argv[1]) or ($class = $argv[1]) == 'help') {
      //print "{} - required param\n";
      //print "[] - param options\n";
      //print "---------\n";
      $classes = (ClassCore::getDescendants('ArrayAccessebleOptions', 'Pm'));
      foreach ($classes as $v) {
        $class = $v['class'];
        foreach ((new ReflectionClass($v['class']))->getMethods() as $method) {
          if (!Misc::hasPrefix('a_', $method->getName())) continue;
          $opt = self::getMethodOptions($method);
          print "pm {$v['name']} ".Misc::removePrefix('a_', $method->getName());
          foreach ($class::$requiredOptions as $vv) print ' '.$vv;
          if (!$opt) {
            print "\n";
            continue;
          }
          foreach ($opt as &$vv) if ($vv[0] == '@') {
            $vv = Misc::removePrefix('@', $vv);
            $method = 'helpOpt_'.$vv;
            if (method_exists($class, $method)) $vv .= '['.($class::$method()).']';
          }
          print ' '.implode(' ', $opt)."\n";
        }
      }
      print "pm localProjects {the same options as localProject}\n";
      print "---------\nprojects:\n";
      foreach (Arr::get((new PmLocalProjectRecords)->getRecords(), 'name') as $v) print "* $v\n";
    } else {
      $class = 'Pm'.ucfirst($class);
      $opt = array_slice($argv, 3);
      $options = [];
      $method = 'a_'.$argv[2];
      foreach ($class::$requiredOptions as $i => $name) $options[$name] = $opt[$i];
      if (!empty($class::$set)) {
        // If static property $set exists, it is multiple wrapper for single processor. And we need to
        // get method options from single processor class.
        (new $class(array_merge($options, $this->getClassMethodOptions($argv, $this->getSingleProcessorClass($class), $method))))->action($argv[2]);
      }
      else {
        (new $class(array_merge($options, $this->getClassMethodOptions($argv, $class, $method, count($options)))))->$method();
      }
    }
  }

  protected function getSingleProcessorClass($multipleProcessorClass) {
    return rtrim($multipleProcessorClass, 's');
  }

  protected function getClassMethodOptions(array $argv, $class, $method, $argvOffset = 0) {
    $options = [];
    if (($optionNames = ($this->getMethodOptions((new ReflectionMethod($class, $method)))))) {
      $from = 3 + $argvOffset;
      foreach (array_slice($argv, $from) as $i => $opt) $options[$optionNames[$i]] = $opt;
    }
    return $options;
  }

  protected function getMethodOptions(ReflectionMethod $method) {
    $options = ClassCore::getDocComment($method->getDocComment(), 'options');
    if (!$options) return false;
    $options = array_map('trim', explode(',', $options));
    foreach ($options as &$v) $v = Misc::removePrefix('@', $v);
    return $options;
  }


  /**
   * @return Tgz
   */
  static function getTgz() {
    //return new Tgz', ['tempFolder' => self::$tempPath]);
  }
  
}

PmManager::$tempPath = NGN_ENV_PATH.'/temp/pm/'.Misc::randString(10);
PmManager::$downloadPath = NGN_ENV_PATH.'/download';
