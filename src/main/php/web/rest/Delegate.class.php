<?php namespace web\rest;

class Delegate {
  private $type, $method;

  public function __construct($type, $method) {
    $this->type= $type;
    $this->method= $method;
  }

  public function invoke($request, $matches) {
    $args= [];
    foreach ($this->method->getParameters() as $param) {
      $name= $param->getName();
      if (isset($matches[$name])) {
        $args[]= $matches[$name];
      } else if (null !== ($arg= $request->param($name))) {
        $args[]= $arg;
      } else if (null !== ($arg= $request->value($name))) {
        $args[]= $arg;
      } else if ($param->isOptional()) {
        $args[]= $param->getDefaultValue();
      }
    }

    return $this->method->invoke($this->type, $args);
  }
}