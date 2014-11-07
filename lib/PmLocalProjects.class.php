<?php

/**
 * Управление группами существующих проектов
 */
class PmLocalProjects extends CliAccessOptionsMultiWrapper {

  protected function records() {
    return (new PmLocalProjectRecords)->getRecords();
  }
/*
  protected function beforeActions() {
    R::set('a', 0);
    setProcessTimeStart('pmProjects');
  }

  protected function afterActions() {
    print "total time: ".round(getProcessTime('pmProjects'), 3)."\n"."iterations: ".count($this->records())."\n";
  }
*/
}
