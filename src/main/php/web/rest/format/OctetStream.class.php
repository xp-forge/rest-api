<?php namespace web\rest\format;

use lang\FormatException;

class OctetStream extends EntityFormat {

  /** @return string */
  public function mimeType() { return 'application/octet-stream'; }

  /**
   * Reads entity from request
   *
   * @param  web.Request $request
   * @param  string $name
   * @return var
   */
  public function read($req, $name) {
    throw new FormatException('Cannot deserialize binary data to an entity');
  }

  /**
   * Writes entity to response
   *
   * @param  web.Response $response
   * @param  string $name
   * @return void
   */
  public function write($req, $value) {
    throw new FormatException('Cannot serialize entities to binary data');
  }
}