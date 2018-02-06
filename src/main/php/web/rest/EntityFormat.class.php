<?php namespace web\rest;

use lang\IllegalArgumentException;

abstract class EntityFormat {
  protected static $READ, $WRITE;

  static function __static() {
    self::$READ= [
      'param'    => function($req, $name) {
        return $req->param($name);
      },
      'value'    => function($req, $name) {
        return $req->value($name);
      },
      'header'   => function($req, $name) {
        return $req->header($name);
      },
      'stream'   => function($req, $name) {
        return $req->stream();
      },
      'default'  => function($req, $name) {
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
    self::$WRITE= [];
  }

  /**
   * Receives arguments from request
   *
   * @param  web.Request $request
   * @param  [:string] $matches
   * @param  [:function(web.Request, var): var] $params
   * @return var[]
   */
  public function arguments($request, $matches, $params) {
    $args= [];
    foreach ($params as $name => $from) {
      if (isset($matches[$name])) {
        $args[]= $matches[$name];
      } else if (null !== ($arg= $from($request, self::$READ))) {
        $args[]= $arg;
      } else {
        throw new IllegalArgumentException('Missing argument '.$name);
      }
    }
    return $args;
  }

  /**
   * Sends a value
   *
   * @param  web.Response $response
   * @param  var $value
   * @return void
   */
  public function value($response, $value) {
    if ($value instanceof Response) {
      $value->transmit($response, self::$WRITE['entity']);
    } else {
      $response->answer(200);
      $f= self::$WRITE['entity'];
      $f($response, $value);
    }
  }

  /**
   * Sends an error
   *
   * @param  web.Response $response
   * @param  int $status Used as HTTP status code
   * @param  lang.Throwable $cause
   * @return void
   */
  public function error($response, $status, $cause) {
    $response->answer($status);
    $f= self::$WRITE['entity'];
    $f($response, ['status'  => $status, 'message' => $cause->getMessage()]);
  }
}