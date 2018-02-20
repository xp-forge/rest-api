<?php namespace web\rest\unittest\paging;

use web\rest\Response;
use web\rest\paging\Pagination;
use web\rest\paging\PageParameters;
use web\Request;
use web\io\TestInput;

class PaginationTest extends \unittest\TestCase {
  const SIZE = 5;

  /**
   * Creates a new request instance
   *
   * @param  string $queryString
   * @return web.rest.paging.Pagination
   */
  protected function newFixture($queryString= '') {
    return new Pagination(
      new Request(new TestInput('GET', '/'.$queryString)),
      new PageParameters('page', 'per_page'),
      self::SIZE
    );
  }

  #[@test]
  public function can_create() {
    $this->newFixture();
  }

  #[@test, @values([
  #  ['', 0],
  #  ['?page=1', 0],
  #  ['?page=2', self::SIZE],
  #  ['?page=1&per_page=10', 0],
  #  ['?page=2&per_page=10', 10]
  #])]
  public function start($queryString, $offset) {
    $this->assertEquals($offset, $this->newFixture($queryString)->start(0));
  }

  #[@test, @values([
  #  ['', self::SIZE],
  #  ['?page=1', self::SIZE],
  #  ['?page=1&per_page=10', 10],
  #  ['?page=2&per_page=10', 20]
  #])]
  public function end($queryString, $offset) {
    $this->assertEquals($offset, $this->newFixture($queryString)->end(0));
  }

  #[@test]
  public function limit_defaults_to_size() {
    $this->assertEquals(self::SIZE, $this->newFixture()->limit());
  }

  #[@test]
  public function limit_explicitely_given() {
    $this->assertEquals(10, $this->newFixture('?per_page=10')->limit());
  }

  #[@test]
  public function paginate_on_empty() {
    $this->newFixture()->paginate([], newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals([], $entity);
  }

  #[@test, @values([
  #  [[1, 2, 3]],
  #  [new \ArrayIterator([1, 2, 3])],
  #  [new \ArrayObject([1, 2, 3])]
  #])]
  public function paginate_on($value) {
    $this->newFixture()->paginate($value, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals([1, 2, 3], $entity);
  }

  #[@test]
  public function paginate_on_generator() {
    $generator= function() { yield 1; yield 2; yield 3; };
    $this->newFixture()->paginate($generator(), newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals([1, 2, 3], $entity);
  }

  #[@test, @values([1, 2, self::SIZE])]
  public function paginate($size) {
    $elements= array_fill(0, $size, 'element');
    $this->newFixture()->paginate($elements, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals($elements, $entity);
  }

  #[@test, @values([1, 2, self::SIZE])]
  public function paginate_removes_excess_elements_from_array($by) {
    $elements= array_fill(0, self::SIZE + $by, 'element');
    $this->newFixture()->paginate($elements, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals(array_fill(0, self::SIZE, 'element'), $entity);
  }

  #[@test, @values([1, 2, self::SIZE])]
  public function paginate_removes_excess_elements_from_generator($by) {
    $generator= function() use($by) {
      for ($i= 0; $i < self::SIZE + $by; $i++) {
        yield 'element';
      }
    };
    $this->newFixture()->paginate($generator(), newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'entity'      => function($value) use(&$entity) { $entity= $value; return $this; }
    ]));
    $this->assertEquals(array_fill(0, self::SIZE, 'element'), $entity);
  }

  #[@test]
  public function link_header_not_present_if_elements_exactly_pagers_size() {
    $elements= array_fill(0, self::SIZE, 'element');
    $headers= [];
    $this->newFixture()->paginate($elements, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals([], $headers);
  }

  #[@test]
  public function link_header_not_present_if_generator_exactly_pagers_size() {
    $generator= function() {
      for ($i= 0; $i < self::SIZE; $i++) {
        yield 'element';
      }
    };
    $headers= [];
    $this->newFixture()->paginate($generator(), newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals([], $headers);
  }

  #[@test]
  public function link_header_to_next_page_present_if_elements_size_exceeds_pager_size() {
    $elements= array_fill(0, self::SIZE + 1, 'element');
    $headers= [];
    $this->newFixture()->paginate($elements, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals(['Link' => '<http://localhost/?page=2>; rel="next"'], $headers);
  }

  #[@test]
  public function link_header_to_next_page_present_if_generator_size_exceeds_pager_size() {
    $generator= function() {
      for ($i= 0; $i < self::SIZE + 1; $i++) {
        yield 'element';
      }
    };
    $headers= [];
    $this->newFixture()->paginate($generator(), newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals(['Link' => '<http://localhost/?page=2>; rel="next"'], $headers);
  }

  #[@test]
  public function link_header_to_previous() {
    $headers= [];
    $this->newFixture('?page=2')->paginate([1, 2, 3], newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals(['Link' => '<http://localhost/?page=1>; rel="prev"'], $headers);
  }


  #[@test]
  public function link_header_to_previous_and_next() {
    $elements= array_fill(0, self::SIZE + 1, 'element');
    $headers= [];
    $this->newFixture('?page=2')->paginate($elements, newinstance(Response::class, [], [
      '__construct' => function() { /* Shadow parent */ },
      'header'      => function($name, $value) use(&$headers) { $headers[$name]= (string)$value; }
    ]));
    $this->assertEquals(['Link' => '<http://localhost/?page=1>; rel="prev", <http://localhost/?page=3>; rel="next"'], $headers);
  }
}