<?php

class CliAccessTestCommandsSingle extends ArrayAccessebleOptions {

  static $requiredOptions = ['name'];

  /**
   * @options something
   */
  function a_one() {
    print $this->options['name'].$this->options['something'];
  }

  /**
   * @options something, {else}
   */
  function a_two() {
    print $this->options['name'].$this->options['something'];
    if (isset($this->options['else'])) print $this->options['else'];
  }

  /**
   * @options something, {@enother}
   */
  function a_three() {
    print $this->options['name'].$this->options['something'];
    if (isset($this->options['enother'])) print $this->options['enother'];
  }

  static function helpOpt_enother() {
    return ['123'];
  }

}
