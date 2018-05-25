<?php namespace web\rest;

class Invocation {
  private $interceptors= [];
  private $target;

  /**
   * Creates a new invocation
   *
   * @param  (web.rest.Interceptor|function(web.rest.Invocation, var[]): var)[] $interceptors
   * @param  web.rest.Delegate $target
   */
  public function __construct($interceptors, $target) {
    foreach ($interceptors as $interceptor) {
      if ($interceptor instanceof Interceptor) {
        $this->interceptors[]= [$interceptor, 'intercept'];
      } else {
        $this->interceptors[]= $interceptor;
      }
    }
    $this->interceptors[]= function($self, $args) { return $this->target->invoke($args); };
    $this->target= $target;
  }

  /** @return web.rest.Delegate */
  public function target() { return $this->target; }

  /**
   * Proceed with the invocation
   *
   * @param  var[] $args
   * @return var
   * @throws lang.Throwable
   */
  public function proceed($args) {
    $i= array_shift($this->interceptors);
    return $i($this, $args);
  }
}