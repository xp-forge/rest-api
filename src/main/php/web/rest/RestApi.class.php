<?php namespace web\rest;

use web\Handler;
use web\routing\CannotRoute;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

class RestApi implements Handler {
  private $delegates= [];

  /** @param object $instance */
  public function __construct($instance) {
    foreach (typeof($instance)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $path) { 
        $pattern= '#^'.$verb.':'.preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $path).'$#';
        $this->delegates[$pattern]= new Delegate($instance, $method);
      }
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
    $format= new Json();

    $match= strtolower($req->method()).':'.$req->uri()->path();
    foreach ($this->delegates as $pattern => $delegate) { 
      if ($c= preg_match($pattern, $match, $matches)) {
        try {
          $format->value($res, $delegate->invoke($format->arguments($req, $matches, $delegate->params())));
        } catch (IllegalArgumentException $e) {
          $format->error($res, 400, $e);
        } catch (TargetInvocationException $e) {
          $format->error($res, 500, $e->getCause());
        }
        return;
      }
    }

    throw new CannotRoute($req);
  }
}