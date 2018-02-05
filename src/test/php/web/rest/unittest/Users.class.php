<?php namespace web\rest\unittest;

class Users {
  private static $USERS= [
    1549 => ['id' => 1549, 'name' => 'Timm'],
    6100 => ['id' => 6100, 'name' => 'Test'],
  ];

  #[@get('/users')]
  public function listUsers() {
    yield 1549 => self::$USERS[1549];
    yield 6100 => self::$USERS[6100];
  }

  #[@get('/users/{id}')]
  public function findUser($id) {
    return self::$USERS[$id];
  }
}