<?php

class PmLocalProjects extends CliHelpOptionsMultiWrapper {

  protected function records() {
    return (new PmLocalProjectRecords)->getRecords();
  }

}
