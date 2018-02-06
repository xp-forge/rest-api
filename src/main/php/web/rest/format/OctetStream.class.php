<?php namespace web\rest\format;

use lang\FormatException;

class OctetStream extends EntityFormat {
  protected $mimeType= 'application/octet-stream';

  public function read($req, $name) {
    throw new FormatException('Cannot deserialize binary data to an entity');
  }

  public function write($req, $value) {
    throw new FormatException('Cannot serialize entities to binary data');
  }
}