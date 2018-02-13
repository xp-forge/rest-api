<?php namespace web\rest\paging;

/**
 * The URL parameters paging behavior uses two parameters from the request's
 * query string to determine page and limit. When the parameter referrning to
 * the current page is omitted from the URL this behavior regards this as the
 * first page. The limit can be used to overwrite the default paging limit set
 * via the `Paging` class.
 */
class PageParameters implements Behavior {
  private $page, $limit;

  /**
   * Creates a new parameters instance
   *
   * @param  string $page Name of parameter referring to the current page
   * @param  string $page Name of parameter referring to the optional limit
   */
  public function __construct($page, $limit) {
    $this->page= $page;
    $this->limit= $limit;
  }

  /**
   * Returns URL with a given page
   *
   * @param  web.Request $request
   * @param  int $page
   * @return util.URI
   */
  private function uriOf($request, $page) {
    return $request->uri()->using()->param($this->page, $page)->create();
  }

  /**
   * Returns whether this behavior paginates a given request. Always returns true,
   * as omitting the page and limit parameters will paginate with the defaults!
   *
   * @param  web.Request $request
   * @return bool
   */
  public function paginates($request) { return true; }

  /**
   * Returns the starting offset set via the request, or NULL if none was given.
   *
   * @param  web.Request $request
   * @param  int $size
   * @return var
   */
  public function start($request, $size) {
    $page= $request->param($this->page, null);
    return null === $page ? null : ($page - 1) * $this->limit($request, $size);
  }

  /**
   * Returns the ending offset set via the request
   *
   * @param  web.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function end($request, $size) {
    return $this->start($request, $size) + $this->limit($request, $size);
  }

  /**
   * Returns the current limit
   *
   * @param  web.Request $request
   * @param  int $size
   * @return int
   */
  public function limit($request, $size) {
    return (int)$request->param($this->limit, $size);
  }

  /**
   * Paginate
   *
   * @param  web.Request $request
   * @param  web.rest.Response $response
   * @param  bool $last
   * @return web.rest.Response
   */
  public function paginate($request, $response, $last) {
    $page= $request->param($this->page, 1);
    $header= new LinkHeader([
      'prev' => $page > 1 ? $this->uriOf($request, $page - 1) : null,
      'next' => $last ? null : $this->uriOf($request, $page + 1)
    ]);

    if ($header->present()) {
      return $response->header('Link', $header);
    } else {
      return $response;
    }
  }
}