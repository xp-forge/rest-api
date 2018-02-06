<?php namespace web\rest\format;

use text\json\Format;
use text\json\StreamOutput;
use text\json\StreamInput;
use lang\IllegalArgumentException;

class Json extends EntityFormat {
  private static $FORMAT;

  static function __static() {
    self::$FORMAT= Format::dense();
  }

  /** @return string */
  public function mimeType() { return 'application/json'; }

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

    $in= new StreamInput($stream);
    try {
      return $in->read();
    } finally {
      $in->close();
    }
  }

  /**
   * Writes entity to response
   *
   * @param  web.Response $response
   * @param  string $name
   * @return void
   */
  public function write($response, $value) {
    $out= new StreamOutput($response->stream(), self::$FORMAT);
    try {
      $out->write($value);
    } finally {
      $out->close();
    }
  }
}