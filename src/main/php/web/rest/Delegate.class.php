<?php namespace web\rest;

class Delegate {
  private $type, $method;

  public function __construct($type, $method) {
    $this->type= $type;
    $this->method= $method;
  }

  public function parameters() { return $this->method->getParameters(); }

  public function invoke($arguments) {
    return $this->method->invoke($this->type, $arguments);
  }
}