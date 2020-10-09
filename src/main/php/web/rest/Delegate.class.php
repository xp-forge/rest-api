<?php namespace web\rest;

use io\streams\{InputStream, Streams};
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;
use web\Request;

class Delegate {
  private static $SOURCES;
  private $instance, $method;
  private $params= [];

  static function __static() {
    self::$SOURCES= [
      'param'    => function($req, $format, $name) { return $req->param($name); },
      'value'    => function($req, $format, $name) { return $req->value($name); },
      'header'   => function($req, $format, $name) { return $req->header($name); },
      'stream'   => function($req, $format, $name) { return $req->stream(); },
      'entity'   => function($req, $format, $name) { return $format->read($req, $name); },
      'request'  => function($req, $format, $name) { return $req; },
      'body'     => function($req, $format, $name) {
        if (null === ($stream= $req->stream())) {
          throw new IllegalArgumentException('Expecting a request body, none transmitted');
        }
        return Streams::readAll($stream);
      },
      'default'  => function($req, $format, $name) {
        if (null !== ($v= $req->param($name))) {
          return $v;
        } else if (null !== ($v= $req->value($name))) {
          return $v;
        } else if (null !== ($v= $req->header($name))) {
          return $v;
        } else {
          return null;
        }
      }
    ];
  }

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

      // Source explicitely set by annotation
      foreach ($param->getAnnotations() as $source => $name) {
        if (isset(self::$SOURCES[$source])) {
          $this->param($param, $name ?? $param->getName(), self::$SOURCES[$source]);
          continue 2;
        }
      }

      // Source derived from parameter type
      $type= $param->getType();
      if ($type->isAssignableFrom(InputStream::class)) {
        $this->param($param, $param->getName(), self::$SOURCES['stream']);
      } else if ($type->isAssignableFrom(Request::class)) {
        $this->param($param, $param->getName(), self::$SOURCES['request']);
      } else {
        $this->param($param, $param->getName(), self::$SOURCES['default']);
      }
    }
  }

  /**
   * Adds parameter request reader for a given parameter
   *
   * @param  lang.reflect.Parameter $param
   * @param  string $name
   * @param  function(web.Request, web.rest.format.EntityFormat, string): var $source
   * @return void
   */
  private function param($param, $name, $source) {
    if ($param->isOptional()) {
      $default= $param->getDefaultValue();
      $read= function($req, $format) use($source, $name, $default) {
        return $source($req, $format, $name) ?? $default;
      };
    } else {
      $read= function($req, $format) use($source, $name) {
        if (null === ($value= $source($req, $format, $name))) {
          throw new IllegalArgumentException('Missing argument '.$name);
        }
        return $value;
      };
    }
    $this->params[$name]= ['type' => $param->getType(), 'read' => $read];
  }

  /** @return string */
  public function name() { return nameof($this->instance).'::'.$this->method->getName(); }

  /** @return [:var] */
  public function annotations() { return $this->method->getAnnotations(); }

  /** @return [:var] */
  public function params() { return $this->params; }

  /**
   * Invokes the delegate
   *
   * @param  var[] $arguments
   * @return var
   * @throws lang.Throwable
   */
  public function invoke($args) {
    try {
      return $this->method->invoke($this->instance, $args);
    } catch (TargetInvocationException $e) {
      throw $e->getCause();
    }
  }
}