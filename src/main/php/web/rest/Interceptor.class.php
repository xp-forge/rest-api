<?php namespace web\rest;

interface Interceptor {

  /**
   * Intercept an invocation
   *
   * @param  web.rest.Invocation $invocation
   * @param  var[] $args
   * @return var
   */
  public function intercept($invocation, $args);
}