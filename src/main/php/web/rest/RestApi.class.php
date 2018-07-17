<?php namespace web\rest;

use lang\IllegalArgumentException;
use lang\Throwable;
use web\Error;
use web\Handler;
use web\rest\format\EntityFormat;
use web\rest\format\Json;
use web\rest\format\OctetStream;
use web\routing\CannotRoute;

class RestApi implements Handler {
  private static $METHODS= [
    'get'     => null,
    'head'    => null,
    'post'    => null,
    'put'     => null,
    'patch'   => null,
    'delete'  => null,
    'options' => null
  ];

  private $formats= [];
  private $delegates= [];
  private $invocations= [];
  private $marshalling;

  /**
   * Creates a new REST API instance for a given handler instance
   *
   * @param  object $instance
   * @param  string $base
   */
  public function __construct($instance, $base= '/') {
    foreach (typeof($instance)->getMethods() as $method) {
      foreach (array_intersect_key($method->getAnnotations(), self::$METHODS) as $verb => $segment) {
        $pattern= $segment
          ? preg_replace(['/\{([^:}]+):([^}]+)\}/', '/\{([^}]+)\}/'], ['(?<$1>$2)', '(?<$1>[^/]+)'], $segment)
          : '.+'
        ;
        $this->delegates['#^'.$verb.':'.rtrim($base, '/').$pattern.'$#']= new Delegate($instance, $method);
      }
    }

    $this->formats['#(application|text)/.*json#']= new Json();
    $this->formats['#application/octet-stream#']= new OctetStream();
    $this->marshalling= new Marshalling();
  }

  /**
   * Register a format
   *
   * @param  string $pattern Mime type regular expression pattern, not including the delimiters!
   * @param  web.rest.EntityFormat $format
   * @return self
   */
  public function register($pattern, EntityFormat $format) {
    $this->formats['#'.preg_quote($pattern, '#').'#i']= $format;
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
   * Determines format from Content-Type header. Defaults to `application/octet-stream`. 
   *
   * @param  string $mime
   * @return web.rest.EntityFormat
   */
  private function format($mime) {
    foreach ($this->formats as $pattern => $format) {
      if (preg_match($pattern, $mime)) return $format;
    }

    return $this->formats['#application/octet-stream#'];
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
    $in= $this->format($req->header('Content-Type') ?: 'application/json');
    $out= $this->format($req->header('Accept') ?: 'application/json');

    $match= strtolower($req->method()).':'.$req->uri()->path();
    foreach ($this->delegates as $pattern => $delegate) { 
      if (preg_match($pattern, $match, $matches)) {
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
        } catch (Throwable $e) {
          return $this->transmit($res, Response::error(500, $e), $out);
        }
      }
    }

    throw new CannotRoute($req);
  }
}