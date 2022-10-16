<?php namespace web\rest;

use web\Headers;

/**
 * Content negotiation
 *
 * @see   https://en.wikipedia.org/wiki/Content_negotiation
 * @test  web.rest.unittest.AcceptTest
 */
class Accept {
  private $values= [];

  /** @param string $header */
  public function __construct($header) {
    $prec= 1.0;
    foreach (Headers::values(Headers::parameterized())->parse($header) as $accept) {
      $value= $accept->value();
      if (null === ($q= $accept->param('q'))) {
        $this->values[$value]= $prec - 0.0001 * substr_count($value, '*');
        $prec-= 0.00001;
      } else {
        $this->values[$value]= (float)$q;
      }
    }
    arsort($this->values, SORT_NUMERIC);
  }

  /**
   * Returns one of the supported mime types, or NULL if none of them match
   * the list of accepted types.
   *
   * @param  string|string[] $supported Supported mime type(s)
   * @return string
   */
  public function match($supported) {
    $s= is_array($supported) ? implode(' ', $supported) : $supported;
    foreach ($this->values as $preference => $q) {
      if (preg_match('#('.strtr(preg_quote($preference, '#'), ['\*' => '[^ ]+']).')#', $s, $matches)) return $matches[1];
    }
    return null;
  }
}