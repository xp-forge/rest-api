<?php namespace web\rest\unittest;

class People {

  /** @var web.rest.unittest.Person[] */
  private $list;

  public function __construct(... $list) {
    $this->list= $list;
  }

  public function all() { return $this->list; }
}