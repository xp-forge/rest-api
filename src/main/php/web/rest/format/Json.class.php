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

  protected function read($req, $name) {
    $in= new StreamInput($req->stream());
    try {
      return $in->read();
    } finally {
      $in->close();
    }
  }

  protected function write($res, $value) {
    $out= new StreamOutput($res->stream(), self::$FORMAT);
    try {
      $out->write($value);
    } finally {
      $out->close();
    }
  }
}