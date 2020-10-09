<?php namespace web\rest;

use lang\IllegalArgumentException;

/**
 * Matches request and routes to correct delegate
 */
class Delegates {
  private static $METHODS= [
    'get'     => 'param',
    'head'    => 'param',
    'post'    => 'entity',
    'put'     => 'entity',
    'patch'   => 'entity',
    'delete'  => 'param',
    'options' => 'param'
  ];
  public $patterns= [];

  /**
   * Routes to instance methods based on annotations
   *
   * @param  object $instance
   * @param  string $base
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public function with($instance, $base= '/') {
    if (!is_object($instance)) {
      throw new IllegalArgumentException('Expected an object, have '.typeof($instance));
    }

    $base= rtrim($base, '/');
    foreach (typeof($instance)->getMethods() as $method) {
      foreach (array_intersect_key($method->getAnnotations(), self::$METHODS) as $verb => $segment) {
        if (null === $segment) {
          $pattern= $base.'(/.+)?';
        } else if ('/' === $segment || '' === $segment) {
          $pattern= $base.'/?';
        } else {
          $pattern= $base.preg_replace(['/\{([^:}]+):([^}]+)\}/', '/\{([^}]+)\}/'], ['(?<$1>$2)', '(?<$1>[^/]+)'], $segment);
        }
        $this->patterns['#^'.$verb.$pattern.'$#']= new Delegate($instance, $method, self::$METHODS[$verb]);
      }
    }
    return $this;
  }

  /**
   * Returns target for a given HTTP verb and path
   *
   * @param  string $verb
   * @param  string $path
   * @return web.frontend.Delegate or NULL
   */
  public function target($verb, $path) {
    $match= $verb.$path;
    foreach ($this->patterns as $pattern => $delegate) {
      if (preg_match($pattern, $match, $matches)) return [$delegate, $matches];
    }
    return null;
  }
}