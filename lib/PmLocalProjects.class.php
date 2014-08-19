<?php

/**
 * Управление группами существующих проектов
 */
class PmLocalProjects extends CliHelpOptionsMultiWrapper {

  protected function records() {
    return (new PmLocalProjectRecords)->getRecords();
  }

}
