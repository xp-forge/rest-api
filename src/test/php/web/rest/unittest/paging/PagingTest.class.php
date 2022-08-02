<?php namespace web\rest\unittest\paging;

use unittest\{Assert, Expect, Test, TestCase};
use util\NoSuchElementException;
use web\Request;
use web\io\TestInput;
use web\rest\paging\{PageParameters, Pagination, Paging};

class PagingTest {

  #[Test]
  public function can_create() {
    new Paging(5, []);
  }

  #[Test]
  public function on_request() {
    $request= new Request(new TestInput('GET', '/'));
    $parameters= new PageParameters('page', 'per_page');

    Assert::equals(
      new Pagination($request, $parameters, 5),
      (new Paging(5, [$parameters]))->on($request)
    );
  }

  #[Test, Expect(NoSuchElementException::class)]
  public function raises_exception_when_no_behavior_applies() {
    (new Paging(5, []))->on(new Request(new TestInput('GET', '/')));
  }
}