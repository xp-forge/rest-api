<?php namespace web\rest\unittest\api;

use io\streams\InputStream;
use io\streams\MemoryInputStream;
use lang\ElementNotFoundException;
use web\Error;
use web\rest\Response;

#[@resource('/users')]
class Users {
  private $users= [
    1549 => ['id' => 1549, 'name' => 'Timm'],
    6100 => ['id' => 6100, 'name' => 'Test'],
  ];

  #[@get('/')]
  public function listUsers() {
    yield 1549 => $this->users[1549];
    yield 6100 => $this->users[6100];
  }

  #[@get('/count')]
  public function numUsers() {
    return Response::ok()->entity(sizeof($this->users));
  }

  #[@get('/{id:[0-9]+}')]
  public function findUser($id) {
    if (!isset($this->users[$id])) {
      throw new ElementNotFoundException('No such user #'.$id);
    }

    return $this->users[$id];
  }

  #[@post('/'), @$user: entity]
  public function newUser($user) {
    end($this->users);
    $id= key($this->users) + 1;
    $new= ['id' => $id, 'name' => $user['name']]; 

    $this->users[$id]= $new;
    return Response::created('/'.$id)->entity($new);
  }

  #[@delete('/{id:[0-9]+}')]
  public function deleteUser($id) {
    throw new Error(402, 'Payment Required');
  }

  #[@get('/{id}/avatar'), @cached(ttl= 3600)]
  public function userAvatar($id) {
    if (!isset($this->users[$id])) {
      return Response::notFound('No such user #'.$id);
    }

    // TBI: Loading from storage
    return Response::ok()->type('image/png')->stream(new MemoryInputStream('PNG...'));
  }

  #[@put('/{id}/avatar')]
  public function changeUserAvatar($id, InputStream $stream) {
    if (!isset($this->users[$id])) {
      return Response::notFound('No such user #'.$id);
    }

    // TBI: Saving to storage
    return Response::noContent();
  }
}