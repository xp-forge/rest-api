<?php namespace web\rest\unittest;

use lang\{ElementNotFoundException, IllegalStateException};
use unittest\Test;
use web\rest\unittest\api\Users;
use web\rest\{Interceptor, Response, RestApi};

class InvocationsTest extends RunTest {

  #[Test]
  public function intercepting() {
    $invocations= newinstance(Interceptor::class, [], [
      'intercept' => function($invocation, $args) use(&$invoked) {
        $invoked= [$invocation->target()->name(), $args];
        return $invocation->proceed($args);
      }
    ]);

    $this->run((new RestApi(new Users()))->intercepting($invocations), 'GET', '/users/1549');
    $this->assertEquals(['web.rest.unittest.api.Users::findUser', ['1549']], $invoked);
  }

  #[Test]
  public function intercepting_with_callable() {
    $invocations= function($invocation, $args) use(&$invoked) {
      $invoked= [$invocation->target()->name(), $args];
      return $invocation->proceed($args);
    };

    $this->run((new RestApi(new Users()))->intercepting($invocations), 'GET', '/users/1549');
    $this->assertEquals(['web.rest.unittest.api.Users::findUser', ['1549']], $invoked);
  }

  #[Test]
  public function intercepting_catching_exceptions() {
    $invocations= function($invocation, $args) use(&$caught) {
      try {
        return $invocation->proceed($args);
      } catch (ElementNotFoundException $e) {
        $caught= [nameof($e), $e->getMessage()];
        return Response::error(404, $e);
      }
    };

    $this->run((new RestApi(new Users()))->intercepting($invocations), 'GET', '/users/0');
    $this->assertEquals(['lang.ElementNotFoundException', 'No such user #0'], $caught);
  }

  #[Test]
  public function intercepting_can_access_annotations() {
    $invocations= function($invocation, $args) use(&$cached) {
      $cached= $invocation->target()->annotations()['cached'];
      return $invocation->proceed($args);
    };

    $this->run((new RestApi(new Users()))->intercepting($invocations), 'GET', '/users/1549/avatar');
    $this->assertEquals(['ttl' => 3600], $cached);
  }

  #[Test]
  public function can_use_multiple_interceptors() {
    $api= (new RestApi(new Users()))
      ->intercepting(function($invocation, $args) use(&$invoked) {
        $invoked[]= 'one';
        return $invocation->proceed($args);
      })
      ->intercepting(function($invocation, $args) use(&$invoked) {
        $invoked[]= 'two';
        return $invocation->proceed($args);
      })
    ;

    $this->run($api, 'GET', '/users/1549/avatar');
    $this->assertEquals(['one', 'two'], $invoked);
  }

  #[Test]
  public function can_break_chain_of_interceptors_by_not_invoking_proceed() {
    $api= (new RestApi(new Users()))
      ->intercepting(function($invocation, $args) {
        return true;
      })
      ->intercepting(function($invocation, $args) {
        throw new IllegalStateException('Will not be reached');
      })
    ;

    $this->run($api, 'GET', '/users/1549/avatar');
  }
}