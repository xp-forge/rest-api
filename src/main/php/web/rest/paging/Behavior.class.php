<?php namespace web\rest\paging;

interface Behavior {

  /**
   * Returns whether this behavior paginates a given request
   *
   * @param  web.Request $request
   * @return bool
   */
  public function paginates($request);

  /**
   * Returns the starting offset set via the request
   *
   * @param  web.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function start($request, $size);

  /**
   * Returns the ending offset set via the request
   *
   * @param  web.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function end($request, $size);

  /**
   * Returns a limit set via the request
   *
   * @param  web.Request $request
   * @param  int $size
   * @return int The limit or NULL if the parameter was omitted
   */
  public function limit($request, $size);

  /**
   * Paginate
   *
   * @param  web.Request $request
   * @param  web.rest.Response $response
   * @param  bool $last
   * @return web.rest.Response
   */
  public function paginate($request, $response, $last);
}