<?php namespace web\rest;

use lang\Reflection;

/**
 * Creates routing based on a given instance
 */
class MethodsIn extends Delegates {

  /** @param object $instance */
  public function __construct($instance) {
    $class= Reflection::type($instance);
    if ($annotation= $class->annotation(Resource::class)) {
      $this->with($instance, (string)$annotation->argument(0));
    } else {
      $this->with($instance, '/');
    }
    uksort($this->patterns, function($a, $b) { return strlen($b) - strlen($a); });
  }
}