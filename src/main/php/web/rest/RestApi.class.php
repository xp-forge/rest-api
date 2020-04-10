<?php namespace web\rest;

use lang\{IllegalArgumentException, Throwable};
use util\data\Marshalling;
use web\{Error, Handler};
use web\rest\format\{EntityFormat, FormUrlEncoded, Json, OctetStream};
use web\routing\CannotRoute;

class RestApi implements Handler {
  private $delegates, $base, $marshalling, $formats;
  private $invocations= [];

  /**
   * Creates a new REST API instance for a given handler
   *
   * @param  web.rest.Delegates|object $arg
   * @param  string $base
   */
  public function __construct($arg, $base= '/') {
    $this->delegates= $arg instanceof Delegates ? $arg : new MethodsIn($arg);
    $this->base= rtrim($base, '/');
    $this->marshalling= new Marshalling();
    $this->formats= [
      'application/json'                  => new Json(),
      'application/x-www-form-urlencoded' => new FormUrlEncoded(),
      'application/octet-stream'          => new OctetStream(),
    ];
  }

  /**
   * Register a format
   *
   * @param  string $mime Mime type
   * @param  web.rest.format.EntityFormat $format
   * @return self
   */
  public function register($mime, EntityFormat $format) {
    $this->formats[$mime]= $format;
    return $this;
  }

  /**
   * Intercept invocations using a given handler
   *
   * @param  web.rest.Interceptor|function(web.rest.Invocation, var[]): var $interceptor
   * @return self
   */
  public function intercepting($interceptor) {
    $this->invocations[]= $interceptor;
    return $this;
  }

  /**
   * Determines format from mime type. Defaults to `application/octet-stream`. 
   *
   * @param  string $mime
   * @return web.rest.format.EntityFormat
   */
  private function format($mime) {
    return isset($this->formats[$mime]) ? $this->formats[$mime] : $this->formats['application/octet-stream'];
  }


  /**
   * Transmits a given result to the response
   *
   * @param  web.Response $res
   * @param  var $result
   * @param  string $format
   * @return void
   */
  private function transmit($res, $result, $format) {
    if ($result instanceof Response) {
      $result->transmit($res, $format, $this->marshalling);
    } else {
      $format->transmit($res, $this->marshalling->marshal($result));
    }
  }

  /**
   * Handle request
   *
   * @param  web.Request $req
   * @param  web.Response $res
   * @return var
   */
  public function handle($req, $res) {
    $in= $this->format($req->header('Content-Type', 'application/json'));

    $accept= new Accept($req->header('Accept', '*/*'));
    if (null === ($format= $accept->match(array_keys($this->formats)))) {
      throw new Error(406, 'Unsupported mime type');
    }
    $out= $this->format($format);

    $verb= strtolower($req->method());
    $path= $this->base ? preg_replace('#^'.$this->base.'#', '', $req->uri()->path()) : $req->uri()->path();
    if (null === ($target= $this->delegates->target($verb, $path))) {
      throw new CannotRoute($req);
    }

    list($delegate, $matches)= $target;
    try {
      $args= [];
      foreach ($delegate->params() as $name => $definition) {
        if (isset($matches[$name])) {
          $args[]= $this->marshalling->unmarshal($matches[$name], $definition['type']);
        } else {
          $args[]= $this->marshalling->unmarshal($definition['read']($req, $in), $definition['type']);
        }
      }
    } catch (IllegalArgumentException $e) {
      return $this->transmit($res, Response::error(400, $e), $out);
    }

    $invocation= new Invocation($this->invocations, $delegate);
    try {
      return $this->transmit($res, $invocation->proceed($args), $out);
    } catch (Error $e) {
      return $this->transmit($res, Response::error($e->status(), $e), $out);
    } catch (Throwable $e) {
      return $this->transmit($res, Response::error(500, $e), $out);
    }
  }
}