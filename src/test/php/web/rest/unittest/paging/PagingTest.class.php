<?php namespace web\rest\unittest\paging;

use unittest\TestCase;
use util\NoSuchElementException;
use web\Request;
use web\io\TestInput;
use web\rest\paging\PageParameters;
use web\rest\paging\Pagination;
use web\rest\paging\Paging;

class PagingTest extends TestCase {

  #[@test]
  public function can_create() {
    new Paging(5, []);
  }

  #[@test]
  public function on_request() {
    $request= new Request(new TestInput('GET', '/'));
    $parameters= new PageParameters('page', 'per_page');

    $this->assertEquals(
      new Pagination($request, $parameters, 5),
      (new Paging(5, [$parameters]))->on($request)
    );
  }

  #[@test, @expect(NoSuchElementException::class)]
  public function raises_exception_when_no_behavior_applies() {
    (new Paging(5, []))->on(new Request(new TestInput('GET', '/')));
  }
}