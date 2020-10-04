<?php namespace web\rest\unittest\paging;

use unittest\Test;
use web\Request;
use web\io\TestInput;
use web\rest\Response;
use web\rest\paging\{LinkHeader, PageParameters};

class PageParametersTest extends \unittest\TestCase {
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

  /**
   * Creates fixture
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new PageParameters('page', 'per_page');
  }

  #[Test]
  public function paginates() {
    $this->assertTrue($this->fixture->paginates($this->newRequest()));
  }

  #[Test]
  public function start_for_empty_request() {
    $this->assertNull($this->fixture->start($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function start_in_request() {
    $this->assertEquals(0, $this->fixture->start($this->newRequest('?page=1'), self::SIZE));
  }

  #[Test]
  public function end_for_empty_request() {
    $this->assertEquals(self::SIZE, $this->fixture->end($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function end_via_limit_in_request() {
    $this->assertEquals(10, $this->fixture->end($this->newRequest('?page=2&per_page=5'), self::SIZE));
  }

  #[Test]
  public function limit_for_empty_request() {
    $this->assertEquals(self::SIZE, $this->fixture->limit($this->newRequest(), self::SIZE));
  }

  #[Test]
  public function limit_in_request() {
    $this->assertEquals(5, $this->fixture->limit($this->newRequest('?per_page=5'), self::SIZE));
  }

  #[Test]
  public function no_headers_when_first_page_is_also_last_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=1'), $response, true);
    $this->assertEquals([], $headers);
  }

  #[Test]
  public function next_header_on_first_page() {
    $response= newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= $value; }
    ]);

    $headers= [];
    $this->fixture->paginate($this->newRequest('?page=1'), $response, false);
    $this->assertEquals(
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
    $this->assertEquals(
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
    $this->assertEquals(
      ['Link' => new LinkHeader(['prev' => 'http://localhost/?page=1'])],
      $headers
    );
  }
}