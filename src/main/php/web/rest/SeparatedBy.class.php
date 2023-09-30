<?php namespace web\rest;

class SeparatedBy implements Conversion {
  private $separator;

  /** @param string $separator */
  public function __construct($separator) {
    $this->separator= $separator;
  }

  /**
   * Converts a given input value.
   *
   * @param  var $input
   * @return var
   */
  public function convert($input) {
    return is_string($input) ? explode($this->separator, $input) : $input;
  }
}