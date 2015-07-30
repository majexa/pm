<?php

class TestCliAccess extends NgnTestCase {

  function test() {
    $this->assertTrue($this->r([
        '### dummy',
        'single',
        'one',
        'myName',
        'something'
      ]) == 'myNamesomething');
    $this->assertTrue($this->r([
        '### dummy',
        'single',
        'two',
        'myName',
        'something'
      ]) == 'myNamesomething');
    $this->assertTrue($this->r([
        '### dummy',
        'single',
        'two',
        'myName',
        'something',
        'else',
      ]) == 'myNamesomethingelse');
    $this->assertTrue($this->r([
        '### dummy',
        'single',
        'three',
        'myName',
        'something',
      ]) == 'myNamesomething');
    $this->assertTrue($this->r([
        '### dummy',
        'single',
        'three',
        'myName',
        'something',
        'abc',
      ]) == 'myNamesomethingabc');
    $this->assertTrue($this->r([
        '### dummy',
        'singles',
        'one',
        'something',
      ]) == 'asomethingbsomething');
    $this->assertTrue($this->r([
        '### dummy',
        'singles',
        'two',
        'something',
      ]) == 'asomethingbsomething');
    $this->assertTrue($this->r([
        '### dummy',
        'singles',
        'two',
        'something',
        'else',
      ]) == 'asomethingelsebsomethingelse');
    $this->assertTrue($this->r([
        '### dummy',
        'singles',
        'three',
        'something',
      ]) == 'asomethingbsomething');
    $this->assertTrue($this->r([
        '### dummy',
        'singles',
        'three',
        'something',
        'enother',
      ]) == 'asomethingenotherbsomethingenother');
  }

  protected function r($r) {
    ob_start();
    new CliAccessCommandsTestWrapper($r);
    return ob_get_clean();
  }

}