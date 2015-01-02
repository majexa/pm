<?php

/**
 * Управление группами существующих проектов
 */
class PmLocalProjects extends CliAccessOptionsMultiWrapper {

  protected function records() {
    return (new PmLocalProjectRecords)->getRecords();
  }

}
