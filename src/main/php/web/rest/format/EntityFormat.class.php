<?php namespace web\rest\format;

use lang\IllegalArgumentException;
use web\rest\Response;

abstract class EntityFormat {
  protected static $READ;
  protected $mimeType= 'application/octet-stream';

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
  }

  protected abstract function read($req, $name);

  protected abstract function write($res, $value);

  /**
   * Receives arguments from request
   *
   * @param  web.Request $request
   * @param  [:string] $matches
   * @param  [:function(web.Request, var): var] $params
   * @return var[]
   */
  public function arguments($request, $matches, $params) {
    $read= self::$READ;
    $read['entity']= function($req, $name) { return $this->read($req, $name); };

    $args= [];
    foreach ($params as $name => $from) {
      if (isset($matches[$name])) {
        $args[]= $matches[$name];
      } else if (null !== ($arg= $from($request, $read))) {
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
    $response->answer(200);
    $response->header('Content-Type', $this->mimeType);

    if ($value instanceof Response) {
      $value->transmit($response, function($res, $value) { $this->write($res, $value); });
    } else {
      $this->write($response, $value);
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
    $response->header('Content-Type', $this->mimeType);

    $this->write($response, ['status'  => $status, 'message' => $cause->getMessage()]);
  }
}