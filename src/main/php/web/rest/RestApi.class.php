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

  /** @param object $instance */
  public function __construct($instance) {
    foreach (typeof($instance)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $path) { 
        $pattern= '#^'.$verb.':'.preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $path).'$#';
        $this->delegates[$pattern]= new Delegate($instance, $method);
      }
    }

    $this->formats['#(application|text)/.*json#']= new Json();
    $this->formats['#application/octet-stream#']= new OctetStream();
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
          $result= $delegate->invoke($format->arguments($req, $matches, $delegate->params()));
          if ($result instanceof Response) {
            $result->transmit($res, $format);
          } else {
            $format->transmit($res, $result);
          }
        } catch (IllegalArgumentException $e) {
          Response::error(400, $e->getMessage())->transmit($res, $format);
        } catch (TargetInvocationException $e) {
          Response::error(500, $e->getCause()->getMessage())->transmit($res, $format);
        }
        return;
      }
    }

    throw new CannotRoute($req);
  }
}