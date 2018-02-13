<?php namespace web\rest\paging;

use web\Request;
use web\rest\Response;

/**
 * A pagination instance holds the paging behavior, the request and the
 * page size. It is created by passing a request instance to the Paging
 * class' `on()` method.
 *
 * @test  xp://web.rest.unittest.paging.PaginationTest
 */
class Pagination {
  private $request, $size, $behavior;

  /**
   * Creates a new pagination instance
   *
   * @param  web.Request $request
   * @param  web.rest.paging.Behavior $behavior
   * @param  int $size
   */
  public function __construct(Request $request, $behavior, $size) {
    $this->request= $request;
    $this->size= $size;
    $this->behavior= $behavior;
  }

  /**
   * Returns the starting offset, or the supplied default of omitted
   *
   * @param  var $default
   * @return var
   */
  public function start($default= null) {
    return $this->behavior->start($this->request, $this->size) ?: $default;
  }

  /**
   * Returns the ending offset, or the supplied default of omitted
   *
   * @param  var $default
   * @return var
   */
  public function end($default= null) {
    return $this->behavior->end($this->request, $this->size) ?: $default;
  }

  /**
   * Returns the limit passed in the request's limit paramenter, or the default limit of omitted
   *
   * @return int
   */
  public function limit() {
    return $this->behavior->limit($this->request, $this->size);
  }

  /**
   * Paginate
   *
   * @param  var[]|iterable $iterable
   * @param  int|web.rest.Response $res
   * @return web.rest.Response
   */
  public function paginate($iterable, $res= 200) {
    $limit= $this->limit();

    // Trim excess elements if necessary
    if ($iterable instanceof \Traversable) {
      $elements= [];
      $i= 0;
      foreach ($iterable as $value) {
        $elements[]= $value;
        if (++$i >= $limit) break;
      }
    } else {
      $elements= (array)$iterable;      
      while (sizeof($elements) > $limit) {
        array_pop($elements);
      }
    }

    $last= sizeof($elements) <= $limit;
    $response= $res instanceof Response ? $res : Response::status($res);
    return $this->behavior->paginate($this->request, $response->entity($elements), $last);
  }
}