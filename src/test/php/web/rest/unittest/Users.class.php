<?php namespace web\rest\unittest;

use web\rest\Response;
use io\streams\MemoryInputStream;

class Users {
  private $users= [
    1549 => ['id' => 1549, 'name' => 'Timm'],
    6100 => ['id' => 6100, 'name' => 'Test'],
  ];

  #[@get('/users')]
  public function listUsers() {
    yield 1549 => $this->users[1549];
    yield 6100 => $this->users[6100];
  }

  #[@get('/users/{id}')]
  public function findUser($id) {
    return $this->users[$id];
  }

  #[@get('/users/{id}/avatar')]
  public function userAvatar($id) {
    return Response::ok()->type('image/png')->stream(new MemoryInputStream('PNG...'));
  }

  #[@post('/users'), @$user: entity]
  public function newUser($user) {
    end($this->users);
    $id= key($this->users) + 1;
    $new= ['id' => $id, 'name' => $user['name']]; 

    $this->users[$id]= $new;
    return Response::created('/users/'.$id)->entity($new);
  }
}