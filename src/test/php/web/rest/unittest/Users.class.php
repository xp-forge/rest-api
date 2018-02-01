<?php namespace web\rest\unittest;

class Users {

  #[@get('/users')]
  public function listUsers() {
    yield 1549 => ['id' => 1549, 'name' => 'Timm'];
    yield 6100 => ['id' => 6100, 'name' => 'Test'];
  }
}