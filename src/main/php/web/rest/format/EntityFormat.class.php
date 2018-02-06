<?php namespace web\rest\format;

use lang\IllegalArgumentException;
use web\rest\Response;

abstract class EntityFormat {
  protected $mimeType= 'application/octet-stream';

  /**
   * Reads entity from request
   *
   * @param  web.Request $request
   * @param  string $name
   * @return var
   */
  public abstract function read($request, $name);

  /**
   * Writes entity to response
   *
   * @param  web.Response $response
   * @param  string $name
   * @return void
   */
  public abstract function write($response, $value);

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
      } else if (null !== ($arg= $from($request, $this))) {
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
      $value->transmit($response, $this);
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