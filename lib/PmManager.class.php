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
          $opt = $this->getMethodOptionsWithMeta($method);
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
      print implode(', ', Arr::get((new PmLocalProjectRecords)->getRecords(), 'name'))."\n";
    } else {
      $class = 'Pm'.ucfirst($class);
      $opt = array_slice($argv, 3);
      $options = [];
      $method = 'a_'.$argv[2];
      foreach ($class::$requiredOptions as $i => $name) $options[$name] = $opt[$i];
      if (!empty($class::$set)) {
        // If static property $set exists, it is multiple wrapper for single processor. And we need to
        // get method options from single processor class.
        if (method_exists($class, $method)) {
          $options = $this->getClassMethodOptions($argv, $class, $method);
        } else {
          $options = $this->getClassMethodOptions($argv, $this->getSingleProcessorClass($class), $method);
        }
        (new $class(array_merge($options, $options)))->action($argv[2]);
      }
      else {
        $_options = $this->getClassMethodOptions($argv, $class, $method, count($options));
        (new $class(array_merge($_options, $options)))->$method();
      }
    }
  }

  protected function getSingleProcessorClass($multipleProcessorClass) {
    return rtrim($multipleProcessorClass, 's');
  }

  protected function getClassMethodOptions(array $argv, $class, $method, $argvOffset = 0) {
    $options = [];
    if (($optionNames = ($this->getMethodOptions((new ReflectionMethod($class, $method)))))) {
      $args = array_slice($argv, 3 + $argvOffset);
      foreach ($optionNames as $i => $opt) {
        if (!isset($args[$i])) throw new Exception("Option '$opt' for method '$method' not defined");
        $options[$opt] = $args[$i];
      }
    }
    return $options;
  }

  protected function getMethodOptions(ReflectionMethod $method) {
    $optionNames = $this->getMethodOptionsWithMeta($method);
    foreach ($optionNames as &$v) $v = Misc::removePrefix('@', $v);
    return $optionNames;
  }

  protected function getMethodOptionsWithMeta(ReflectionMethod $method) {
    $options = ClassCore::getDocComment($method->getDocComment(), 'options');
    if (!$options) return [];
    return array_map('trim', explode(',', $options));
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
