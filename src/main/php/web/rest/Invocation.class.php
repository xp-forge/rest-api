<?php namespace web\rest;

class Invocation {
  private $interceptors, $target;

  /**
   * Creates a new invocation
   *
   * @param  (function(web.rest.Invocation, var[]): var)[] $interceptors
   * @param  web.rest.Delegate $target
   */
  public function __construct($interceptors, $target) {
    $this->interceptors= $interceptors;
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