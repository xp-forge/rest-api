<?php namespace web\rest;

class Delegate {
  private static $SOURCES= ['param', 'value', 'header', 'stream', 'entity'];

  private $instance, $method;
  private $params= [];

  /**
   * Creates a new delegate
   *
   * @param  object $instance
   * @param  lang.reflect.Method $method
   */
  public function __construct($instance, $method) {
    $this->instance= $instance;
    $this->method= $method;
    foreach ($method->getParameters() as $param) {
      foreach ($param->getAnnotations() as $source => $name) {
        if (isset(self::$SOURCES[$source])) {
          $this->param($param, $name, $source);
          continue 2;
        }
      }
      $this->param($param, $param->getName(), 'default');
    }
  }

  /**
   * Adds parameter request reader for a given parameter
   *
   * @param  lang.reflect.Parameter $param
   * @param  string $name
   * @param  string $source
   */
  private function param($param, $name, $source) {
    if ($param->isOptional()) {
      $default= $param->getDefaultValue();
      $this->params[$name]= function($req, $read) use($source, $name, $default) {
        return null === ($value= $read[$source]($req, $name)) ? $default : $value;
      };
    } else {
      $this->params[$name]= function($req, $read) use($source, $name) {
        return $read[$source]($req, $name);
      };
    }
  }

  /** @return string */
  public function name() { return nameof($this->instance).'::'.$this->method->getName(); }

  /** @return [:function(web.Request, var): var] */
  public function params() { return $this->params; }

  /**
   * Invokes the delegate
   *
   * @param  var[] $arguments
   * @return var
   * @throws lang.reflect.TargetInvocationException
   */
  public function invoke($args) {
    return $this->method->invoke($this->instance, ...$args);
  }
}