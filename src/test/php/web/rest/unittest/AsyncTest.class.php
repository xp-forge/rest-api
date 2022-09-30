<?php namespace web\rest\unittest;

use unittest\{Assert, Expect, Test};
use web\rest\{Async, RestApi, Get, Response};

class AsyncTest extends RunTest {

  #[Test]
  public function async_returning_response() {
    $res= $this->run(new RestApi(new class() {

      #[Get('/')]
      public function run() {
        return new Async(function() {
          yield;
          return Response::noContent();
        });
      }
    }));

    Assert::equals(204, $res->status());
  }

  #[Test]
  public function async_returning_value() {
    $res= $this->run(new RestApi(new class() {

      #[Get('/')]
      public function run() {
        return new Async(function() {
          yield;
          return ['success' => true];
        });
      }
    }));

    $this->assertPayload(200, 'application/json', '{"success":true}', $res);
  }

  #[Test]
  public function async_without_yield() {
    $res= $this->run(new RestApi(new class() {

      #[Get('/')]
      public function run() {
        return new Async(function() {
          return Response::ok();
        });
      }
    }));

    Assert::equals(200, $res->status());
  }
}