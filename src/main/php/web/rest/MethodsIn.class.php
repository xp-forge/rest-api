<?php namespace web\rest;

use lang\Reflection;

/**
 * Creates routing based on a given instance
 */
class MethodsIn extends Delegates {

  /** @param object $instance */
  public function __construct(object $instance) {
    $class= Reflection::type($instance);
    if ($annotation= $class->annotation(Resource::class)) {
      $this->with($instance, (string)$annotation->argument(0));
    } else {
      $this->with($instance, '/');
    }
    uksort($this->patterns, fn($a, $b) => strlen($b) - strlen($a));
  }
}