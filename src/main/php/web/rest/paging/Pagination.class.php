<?php namespace web\rest\paging;

use web\Request;

/**
 * A pagination instance holds the paging behavior, the request and the
 * page size. It is created by passing a request instance to the Paging
 * class' `on()` method.
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
   * @param  web.rest.Response $response
   * @param  var[]|iterable $iterable
   * @return web.rest.Response
   */
  public function paginate($response, $iterable) {
    if ($iterable instanceof \Traversable) {
      $elements= [];
      foreach ($iterable as $value) {
        $elements[]= $value;
      }
    } else {
      $elements= (array)$iterable;      
    }

    $limit= $this->limit();
    $last= sizeof($elements) <= $limit;
    while (sizeof($elements) > $limit) {
      array_pop($elements);
    }

    return $this->behavior->paginate($this->request, $response, $last)->entity($elements);
  }
}