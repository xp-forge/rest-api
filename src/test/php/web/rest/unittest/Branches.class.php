<?php namespace web\rest\unittest;

use lang\IllegalArgumentException;
use web\rest\Conversion;

class Branches implements Conversion {

  public function convert($input) {
    if (2 === sscanf($input, '%[^.]...%[^.]', $lo, $hi)) {
      return [$lo, $hi];
    }
    throw new IllegalArgumentException('Malformed input "'. $input.'"');
  }
}