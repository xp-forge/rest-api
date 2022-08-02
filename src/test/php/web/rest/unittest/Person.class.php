<?php namespace web\rest\unittest;

class Person {
  private static $ROOT = 0;

  /** @var int */
  private $id;

  /** @var string */
  public $name;

  public function __construct($id, $name) {
    $this->id= $id;
    $this->name= $name;
  }

  public function id() { return $this->id; }
}