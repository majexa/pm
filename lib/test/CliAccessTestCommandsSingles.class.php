<?php

class CliAccessTestCommandsSingles extends CliAccessOptionsMultiWrapper {

  protected function records() {
    return [
      ['name' => 'a'],
      ['name' => 'b']
    ];
  }

}