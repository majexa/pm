<?php

/**
 * Управление группами существующих проектов
 */
class PmLocalProjects extends CliAccessOptionsMultiWrapper {

  protected function records() {
    return array_filter((new PmLocalProjectRecords)->getRecords(), function(array $record) {
      if (!(new PmProjectConfig($record['name']))->isNgnProject()) return false;
      return true;
    });
  }

}
