<?php namespace web\rest\unittest\api;

use io\streams\{InputStream, MemoryInputStream};
use lang\ElementNotFoundException;
use web\Error;
use web\rest\{Delete, Get, SeparatedBy, Param, Post, Put, Resource, Response, Value};

#[Resource('/users')]
class Users {
  private $users= [
    1549 => ['id' => 1549, 'handle' => 'thekid', 'name' => 'Timm'],
    6100 => ['id' => 6100, 'handle' => 'test', 'name' => 'Test'],
    6101 => ['id' => 6101, 'handle' => 'binford', 'name' => 'More Power'],
  ];

  #[Get('/')]
  public function listUsers(
    #[Param, SeparatedBy(',')]
    $select= []
  ) {
    if (empty($select)) {
      yield from $this->users;
    } else {
      foreach ($this->users as $id => $user) {
        if (in_array($user['handle'], $select)) yield $id => $user;
      }
    }
  }

  #[Get('/count')]
  public function numUsers() {
    return Response::ok()->entity(sizeof($this->users));
  }

  #[Get('/{id:[0-9]+}')]
  public function findUserById($id) {
    if (!isset($this->users[$id])) {
      throw new ElementNotFoundException('No such user #'.$id);
    }

    return $this->users[$id];
  }

  #[Get('/@{handle:[a-z]+}')]
  public function findUserByName($handle) {
    foreach ($this->users as $user) {
      if ($handle === $user['handle']) return $user;
    }

    throw new ElementNotFoundException('No such user @'.$handle);
  }

  #[Post('/')]
  public function newUser($user) {
    end($this->users);
    $id= key($this->users) + 1;
    $new= ['id' => $id, 'name' => $user['name']]; 

    $this->users[$id]= $new;
    return Response::created('/'.$id)->entity($new);
  }

  #[Delete('/{id:[0-9]+}')]
  public function deleteUser($id) {
    throw new Error(402, 'Payment Required');
  }

  #[Delete('/')]
  public function deleteAllUsers(
    #[Value('user')]
    $auth
  ) {
    $this->users= [];
    return Response::noContent();
  }

  #[Get('/{id}/avatar'), Cached(ttl: 3600)]
  public function userAvatar($id) {
    if (!isset($this->users[$id])) {
      return Response::notFound('No such user #'.$id);
    }

    // TBI: Loading from storage
    return Response::ok()->type('image/png')->stream(new MemoryInputStream('PNG...'));
  }

  #[Put('/{id}/avatar')]
  public function changeUserAvatar($id, InputStream $stream) {
    if (!isset($this->users[$id])) {
      return Response::notFound('No such user #'.$id);
    }

    // TBI: Saving to storage
    return Response::noContent();
  }
}