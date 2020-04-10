<?php namespace web\rest\format;

use io\streams\Streams;
use lang\{FormatException, IllegalArgumentException};

class FormUrlEncoded extends EntityFormat {

  /** @return string */
  public function mimeType() { return 'application/x-www-form-urlencoded'; }

  /**
   * Reads entity from request
   *
   * @param  web.Request $request
   * @param  string $name
   * @return var
   */
  public function read($request, $name) {
    if (null === ($stream= $request->stream())) {
      throw new IllegalArgumentException('Expecting a request body, none transmitted');
    }

    parse_str(Streams::readAll($stream), $query);
    return $query;
  }

  /**
   * Writes entity to response
   *
   * @param  web.Response $response
   * @param  string $name
   * @return void
   */
  public function write($response, $value) {
    throw new FormatException('Cannot serialize entities to application/x-www-form-urlencoded');
  }
}