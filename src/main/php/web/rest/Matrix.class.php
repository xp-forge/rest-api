<?php namespace web\rest;

class Matrix extends Conversion {

  /**
   * Converts a given input value.
   *
   * @param  var $input
   * @return var
   */
  public function convert($input) {
    if (is_string($input)) {
      parse_str(strtr($input, ['&' => '%26', ';' => '&']), $result);
      return $result;
    }
    return $input;
  }
}