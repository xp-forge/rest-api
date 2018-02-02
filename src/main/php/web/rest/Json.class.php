<?php namespace web\rest;

use text\json\Format;
use text\json\StreamOutput;
use text\json\StreamInput;

class Json extends EntityFormat {

  static function __static() {
    parent::__static();
    $format= Format::dense();

    self::$READ['entity']= function($req, $name) {
      $in= new StreamInput($req->stream());
      try {
        return $in->read();
      } finally {
        $in->close();
      }
    };
    self::$WRITE['entity']= function($res, $value) use($format) {
      $res->header('Content-Type', 'application/json');
      $out= new StreamOutput($res->stream(), $format);
      try {
        $out->write($value);
      } finally {
        $out->close();
      }
    };
  }
}