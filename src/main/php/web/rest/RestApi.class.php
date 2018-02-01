<?php namespace web\rest;

use web\Handler;
use web\Error;
use web\routing\CannotRoute;
use lang\reflect\TargetInvocationException;
use lang\IllegalArgumentException;

class RestApi implements Handler {
  private $delegates= [];

  public function __construct($implementation) {
    foreach (typeof($implementation)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $path) { 
        $pattern= '#^'.$verb.':'.preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $path).'$#';
        $this->delegates[$pattern]= new Delegate($implementation, $method);
      }
    }
  }

  public function handle($req, $res) {
    $match= strtolower($req->method()).':'.$req->uri()->path();
    foreach ($this->delegates as $pattern => $delegate) { 
      if ($c= preg_match($pattern, $match, $matches)) { 
        try {
          $delegate->invoke($req, $res, $matches);
        } catch (IllegalArgumentException $e) {
          throw new Error(400, 'Errors invoking '.$method->getName(), $e->getCause());
        } catch (TargetInvocationException $e) {
          throw new Error(500, 'Errors invoking '.$method->getName(), $e->getCause());
        }
      }
    }

    throw new CannotRoute($req);
  }
}