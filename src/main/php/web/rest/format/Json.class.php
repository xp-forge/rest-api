<?php namespace web\rest\format;

use text\json\Format;
use text\json\StreamOutput;
use text\json\StreamInput;

class Json extends EntityFormat {
  private static $FORMAT;
  protected $mimeType= 'application/json';

  static function __static() {
    self::$FORMAT= Format::dense();
  }

  public function read($request, $name) {
    $in= new StreamInput($request->stream());
    try {
      return $in->read();
    } finally {
      $in->close();
    }
  }

  public function write($response, $value) {
    $out= new StreamOutput($response->stream(), self::$FORMAT);
    try {
      $out->write($value);
    } finally {
      $out->close();
    }
  }
}