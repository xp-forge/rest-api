<?php namespace web\rest;

use io\streams\{InputStream, Streams};
use lang\reflection\{Method, TargetException};
use lang\{IllegalArgumentException, Reflection, Type};
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
    ];
  }

  /**
   * Creates a new delegate
   *
   * @param  object $instance
   * @param  string|lang.reflection.Method $method
   * @param  string $source Default source
   */
  public function __construct($instance, $method, $source) {
    $this->instance= $instance;
    $this->method= $method instanceof Method ? $method : Reflection::type($instance)->method($method);
    foreach ($this->method->parameters() as $param) {

      // Check for source being explicitely set by annotation
      $accessor= null;
      $conversions= [];
      foreach ($param->annotations() as $annotation) {
        if (null === $accessor && $accessor= self::$SOURCES[$annotation->name()] ?? null) {
          $name= $annotation->argument(0) ?? $param->name();
        } else if ($annotation->is(Conversion::class)) {
          $conversions[]= $annotation->newInstance();
        }
      }

      // Otherwise try to derive source from parameter type, falling
      // back to the one supplied via constructor parameter.
      if (null === $accessor) {
        $name= $param->name();
        $type= $param->constraint()->type();
        if (Type::$VAR === $type) {
          goto supplied;
        } else if ($type->isAssignableFrom(InputStream::class)) {
          $accessor= self::$SOURCES['stream'];
        } else if ($type->isAssignableFrom(Request::class)) {
          $accessor= self::$SOURCES['request'];
        } else {
          supplied: $accessor= self::$SOURCES[$source];
        }
      }

      $this->param($param, $name, $accessor, $conversions);
    }
  }

  /**
   * Adds parameter request accessor for a given parameter
   *
   * @param  lang.reflection.Parameter $param
   * @param  string $name
   * @param  function(web.Request, web.rest.format.EntityFormat, string): var $accessor
   * @param  web.rest.Conversion[] $conversions
   * @return void
   * @throws lang.IllegalArgumentException
   */
  private function param($param, $name, $accessor, $conversions= []) {
    if ($param->optional()) {
      $default= $param->default();
      $read= function($req, $format) use($accessor, $name, $default) {
        return $accessor($req, $format, $name) ?? $default;
      };
    } else {
      $read= function($req, $format) use($accessor, $name) {
        if (null === ($value= $accessor($req, $format, $name))) {
          throw new IllegalArgumentException('Missing argument '.$name);
        }
        return $value;
      };
    }
    $this->params[$name]= ['type' => $param->constraint()->type(), 'read' => $read, 'conv' => $conversions];
  }

  /** @return string */
  public function name() { return nameof($this->instance).'::'.$this->method->name(); }

  /** @return lang.reflection.Annotations */
  public function annotations() { return $this->method->annotations(); }

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
    } catch (TargetException $e) {
      throw $e->getCause();
    }
  }
}