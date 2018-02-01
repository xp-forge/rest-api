<?php namespace web\rest;

use lang\IllegalArgumentException;
use text\json\Format;
use text\json\StreamOutput;
use text\json\StreamInput;

class Delegate {
  private $instance, $method;
  private $params= [];

  private static $READ, $WRITE;

  static function __static() {
    self::$READ= [
      'param'    => function($req, $name, $default= null) {
        return $req->param($name, $default);
      },
      'value'    => function($req, $name, $default= null) {
        return $req->value($name, $default);
      },
      'header'   => function($req, $name, $default= null) {
        return $req->header($name, $default);
      },
      'stream'   => function($req, $name, $default= null) {
        return $req->stream();
      },
      'entity'   => function($req, $name, $default= null) {
        $in= new StreamInput($req->stream());
        try {
          return $in->read();
        } finally {
          $in->close();
        }
      },
      'default'  => function($req, $name, $default= null) {
        if (null !== ($v= $req->param($name))) {
          return $v;
        } else if (null !== ($v= $req->value($name))) {
          return $v;
        } else if (null !== ($v= $req->header($name))) {
          return $v;
        } else {
          return $default;
        }
      }
    ];
    self::$WRITE= Format::dense();
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
      foreach ($this->source($param) as $name => $source) {
        if ($param->isOptional()) {
          $default= $param->getDefaultValue();
          $this->params[$name]= function($req, $name) use($source, $default) {
            $f= self::$READ[$source];
            return $f($req, $name, $default);
          };
        } else {
          $this->params[$name]= self::$READ[$source];
        }
      }
    }
  }

  /**
   * Returns a parameter's source
   *
   * @param  lang.reflect.Parameter $param
   * @return [:string]
   */
  private function source($param) {
    foreach ($param->getAnnotations() as $source => $name) {
      if (isset(self::$READ[$source])) {
        return [$name ?: $param->getName() => $source];
      }
    }
    return [$param->getName() => 'default'];
  }

  /**
   * Invokes the delegate
   *
   * @param  web.Request $request
   * @param  web.Request $response
   * @param  [:string] $matches
   * @return var
   */
  public function invoke($request, $response, $matches) {

    // Compute arguments
    $args= [];
    foreach ($this->params as $name => $read) {
      if (isset($matches[$name])) {
        $args[]= $matches[$name];
      } else if (null !== ($arg= $read($request, $name))) {
        $args[]= $arg;
      } else {
        throw new IllegalArgumentException('Missing argument "'.$name.'"');
      }
    }

    $result= $this->method->invoke($this->instance, $args);

    // Stream output
    $response->answer(200);
    $response->header('Content-Type', 'application/json');
    $out= new StreamOutput($response->stream(), self::$WRITE);
    try {
      $out->write($result);
    } finally {
      $out->close();
    }
  }
}