<?php namespace web\rest;

class Accept {
  public $values= [];

  public function __construct($header) {
    $prec= 1.0;
    foreach (explode(',', $header) as $t) {
      preg_match('# ?(.+); ?q=([0-9\.]+)#', $t, $matches);
      if (empty($matches)) {
        $this->values[trim($t, ' ')]= $prec - 0.00001 * substr_count($t, '*') + 0.0001 * substr_count($t, ';');
        $prec-= 0.000001;
      } else {
        $this->values[$matches[1]]= (float)$matches[2];
      }
    }
    arsort($this->values, SORT_NUMERIC);
  }

  public function matches($supported) {
    $s= is_array($supported) ? implode(' ', $supported) : $supported;
    foreach ($this->values as $preference => $q) {
      if (preg_match('#('.strtr(preg_quote($preference, '#'), ['\*' => '[^ ]+']).')#', $s, $matches)) return $matches[1];
    }
    return null;
  }
}