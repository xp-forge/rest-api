<?php namespace web\rest\unittest\paging;

use test\{Assert, Before, Test};
use web\Request;
use web\io\TestInput;
use web\rest\Response;
use web\rest\paging\{LinkHeader, PageParameters};

class PageParametersTest {
  const SIZE = 20;

  private $fixture;

  /**
   * Creates a new request instance
   *
   * @param  string $queryString
   * @return web.Request
   */
  private function newRequest($queryString= '') {
    return new Request(new TestInput('GET', '/'.$queryString));
  }

  #[Before]
  public function fixture() {
    $this->fixture= new PageParameters('page', 'per_page');
  }

  #[Test]
  public function paginates() {
    Assert::true($this->fixture->paginates($this->newRequest()));
  }

  #[Test]
  public function start_for_empty_request() {
    Assert::null($this->fixture->start($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function start_in_request() {
    Assert::equals(0, $this->fixture->start($this->newRequest('?page=1'), self::SIZE));
  }

  #[Test]
  public function end_for_empty_request() {
    Assert::equals(self::SIZE, $this->fixture->end($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function end_via_limit_in_request() {
    Assert::equals(10, $this->fixture->end($this->newRequest('?page=2&per_page=5'), self::SIZE));
  }

  #[Test]
  public function limit_for_empty_request() {
    Assert::equals(self::SIZE, $this->fixture->limit($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function limit_in_request() {
    Assert::equals(5, $this->fixture->limit($this->newRequest('?per_page=5'), self::SIZE));
  }

  #[Test]
  public function no_headers_when_first_page_is_also_last_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=1'), $response, true);
    Assert::equals([], $headers);
  }

  #[Test]
  public function next_header_on_first_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=1'), $response, false);
    Assert::equals(
      ['Link' => new LinkHeader(['next' => 'http://localhost/?page=2'])],
      $headers
    );
  }

  #[Test]
  public function next_and_prev_header_on_second_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=2'), $response, false);
    Assert::equals(
      ['Link' => new LinkHeader(['prev' => 'http://localhost/?page=1', 'next' => 'http://localhost/?page=3'])],
      $headers
    );
  }

  #[Test]
  public function prev_header_on_last_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=2'), $response, true);
    Assert::equals(
      ['Link' => new LinkHeader(['prev' => 'http://localhost/?page=1'])],
      $headers
    );
  }
}