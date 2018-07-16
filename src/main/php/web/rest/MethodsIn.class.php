<?php namespace web\rest;

/**
 * Creates routing based on a given instance
 */
class MethodsIn extends Delegates {

  /** @param object $instance */
  public function __construct($instance) {
    $this->with($instance);
    uksort($this->patterns, function($a, $b) { return strlen($b) - strlen($a); });
  }
}