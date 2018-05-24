<?php namespace web\rest;

use web\Handler;
use web\Error;
use web\rest\format\EntityFormat;
use web\rest\format\Json;
use web\rest\format\OctetStream;
use web\routing\CannotRoute;
use lang\IllegalArgumentException;
use lang\Throwable;

class RestApi implements Handler {
  private static $METHODS= ['get', 'head', 'post', 'put', 'patch', 'delete', 'options'];

  private $formats= [];
  private $delegates= [];
  private $marshalling, $invocations;

  /**
   * Creates a new REST API instance for a given handler instance
   *
   * @param  object $instance
   * @param  string $base
   */
  public function __construct($instance, $base= '/') {
    foreach (typeof($instance)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $segment) {
        if (in_array($verb, self::$METHODS)) {
          $pattern= $segment
            ? preg_replace(['/\{([^:}]+):([^}]+)\}/', '/\{([^}]+)\}/'], ['(?<$1>$2)', '(?<$1>[^/]+)'], $segment)
            : '.+'
          ;
          $this->delegates['#^'.$verb.':'.rtrim($base, '/').$pattern.'$#']= new Delegate($instance, $method);
        }
      }
    }

    $this->formats['#(application|text)/.*json#']= new Json();
    $this->formats['#application/octet-stream#']= new OctetStream();
    $this->marshalling= new Marshalling();
    $this->invocations= function($delegate, $args) { return $delegate->invoke($args); };
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
   * @param  function(web.rest.Delegate, var[]): var $invocations
   * @return self
   */
  public function intercepting($invocations) {
    $this->invocations= $invocations;
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
    $format= $this->format($req->header('Content-Type') ?: 'application/json');

    $match= strtolower($req->method()).':'.$req->uri()->path();
    foreach ($this->delegates as $pattern => $delegate) { 
      if (preg_match($pattern, $match, $matches)) {
        try {
          $args= [];
          foreach ($delegate->params() as $name => $definition) {
            if (isset($matches[$name])) {
              $args[]= $this->marshalling->unmarshal($matches[$name], $definition['type']);
            } else {
              $args[]= $this->marshalling->unmarshal($definition['read']($req, $format), $definition['type']);
            }
          }
        } catch (IllegalArgumentException $e) {
          return $this->transmit($res, Response::error(400, $e), $format);
        }

        try {
          $i= $this->invocations;
          return $this->transmit($res, $i($delegate, $args), $format);
        } catch (Throwable $e) {
          return $this->transmit($res, Response::error(500, $e), $format);
        }
      }
    }

    throw new CannotRoute($req);
  }
}