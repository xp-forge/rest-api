<?php namespace web\rest;

use io\streams\InputStream;
use io\streams\Streams;
use lang\XPClass;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

class Delegate {
  private static $SOURCES;
  private static $INPUTSTREAM;
  private $instance, $method;
  private $params= [];

  static function __static() {
    self::$INPUTSTREAM= new XPClass(InputStream::class);
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
      foreach ($param->getAnnotations() as $source => $name) {
        if (isset(self::$SOURCES[$source])) {
          $this->param($param, $name ?: $param->getName(), $source);
          continue 2;
        }
      }

      $source= self::$INPUTSTREAM->isAssignableFrom($param->getType()) ? 'stream' : 'default';
      $this->param($param, $param->getName(), $source);
    }
  }

  /**
   * Adds parameter request reader for a given parameter
   *
   * @param  lang.reflect.Parameter $param
   * @param  string $name
   * @param  string $source
   * @return void
   */
  private function param($param, $name, $source) {
    if ($param->isOptional()) {
      $default= $param->getDefaultValue();
      $read= function($req, $format) use($source, $name, $default) {
        $f= self::$SOURCES[$source];
        if (null === ($value= $f($req, $format, $name))) {
          return $default;
        }
        return $value;
      };
    } else {
      $read= function($req, $format) use($source, $name) {
        $f= self::$SOURCES[$source];
        if (null === ($value= $f($req, $format, $name))) {
          throw new IllegalArgumentException('Missing argument '.$name);
        }
        return $value;
      };
    }
    $this->params[$name]= ['type' => $param->getType(), 'read' => $read];
  }

  /** @return string */
  public function name() { return nameof($this->instance).'::'.$this->method->getName(); }

  /** @return [:function(web.Request, web.rest.format.EntityFormat): var] */
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