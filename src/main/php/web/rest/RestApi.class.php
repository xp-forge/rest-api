<?php namespace web\rest;

use web\Handler;
use web\Error;
use web\rest\format\EntityFormat;
use web\rest\format\Json;
use web\rest\format\OctetStream;
use web\routing\CannotRoute;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

class RestApi implements Handler {
  private $formats= [];
  private $delegates= [];
  private $marshalling;

  /**
   * Creates a new REST API instance for a given handler instance
   *
   * @param  object $instance
   * @param  string $base
   */
  public function __construct($instance, $base= '/') {
    foreach (typeof($instance)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $path) { 
        $pattern= '#^'.$verb.':'.rtrim($base, '/').preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $path).'$#';
        $this->delegates[$pattern]= new Delegate($instance, $method);
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
            } else if (null !== ($arg= $definition['read']($req, $format))) {
              $args[]= $this->marshalling->unmarshal($arg, $definition['type']);
            } else {
              throw new IllegalArgumentException('Missing argument '.$name);
            }
          }

          $result= $delegate->invoke($args);
        } catch (IllegalArgumentException $e) {
          $result= Response::error(400, $e);
        } catch (TargetInvocationException $e) {
          $result= Response::error(500, $e->getCause());
        }

        if ($result instanceof Response) {
          $result->transmit($res, $format, $this->marshalling);
        } else {
          $format->transmit($res, $this->marshalling->marshal($result));
        }
        return;
      }
    }

    throw new CannotRoute($req);
  }
}