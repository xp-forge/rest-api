<?php namespace web\rest;

/**
 * Creates routing based on a given instance
 */
class MethodsIn extends Delegates {

  /** @param object $instance */
  public function __construct($instance) {
    $class= typeof($instance);
    $this->with($instance, $class->hasAnnotation('resource') ? $class->getAnnotation('resource') : '/');
    uksort($this->patterns, function($a, $b) { return strlen($b) - strlen($a); });
  }
}