<?php namespace web\rest;

/** @see https://doriantaylor.com/policy/http-url-path-parameter-syntax */
class Matrix extends Conversion {

  /**
   * Converts a given input value.
   *
   * @param  var $input
   * @return var
   */
  public function convert($input) {
    if (is_string($input)) {
      $matrix= [];
      for ($o= 0, $l= strlen($input); $o < $l; $o+= $s + 1) {
        $s= strcspn($input, ';', $o);
        if (1 === sscanf(substr($input, $o, $s), '%[^=]=%[^;]', $key, $value)) {
          $matrix[$key]= null;
        } else {
          $matrix[$key]= false === strpos($value, ',') ? $value : explode(',', $value);
        }
      }
      return $matrix;
    }
    return $input;
  }
}