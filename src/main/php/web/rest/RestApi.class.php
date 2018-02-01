<?php namespace web\rest;

use lang\XPClass;
use util\Objects;
use web\Handler;
use web\Error;
use web\routing\CannotRoute;
use text\json\Format;
use text\json\StreamOutput;
use lang\reflect\TargetInvocationException;

class RestApi implements Handler {
  private $patterns= [];
  private $format;

  public function __construct($implementation) {
    $this->format= Format::dense();
    foreach (typeof($implementation)->getMethods() as $method) {
      foreach ($method->getAnnotations() as $verb => $path) { 
        $pattern= '#^'.$verb.':'.preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $path).'$#';
        $this->patterns[$pattern]= new Delegate($implementation, $method);
      }
    }
  }

  public function handle($req, $res) {
    $match= strtolower($req->method()).':'.$req->uri()->path();
    foreach ($this->patterns as $pattern => $delegate) { 
      if ($c= preg_match($pattern, $match, $matches)) { 
        $args= [];
        foreach ($delegate->parameters() as $param) {
          $name= $param->getName();
          if (isset($matches[$name])) {
            $args[]= $matches[$name];
          } else if (null !== ($arg= $req->param($name))) {
            $args[]= $arg;
          } else if (null !== ($arg= $req->value($name))) {
            $args[]= $arg;
          } else if ($param->isOptional()) {
            $args[]= $param->getDefaultValue();
          }
        }

        try {
          $result= $delegate->invoke($args);
        } catch (TargetInvocationException $e) {
          throw new Error(500, 'Errors invoking '.$method->getName(), $e->getCause());
        }

        // Stream response
        $res->answer(200);
        $res->header('Content-Type', 'application/json');

        $out= new StreamOutput($res->stream(), $this->format);
        try {
          $out->write($result);
        } finally {
          $out->close();
        }
        return;
      }
    }

    throw new CannotRoute($req);
  }
}