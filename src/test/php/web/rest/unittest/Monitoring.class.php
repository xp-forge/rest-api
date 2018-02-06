<?php namespace web\rest\unittest;

use web\rest\Response;
use util\Date;
use util\Bytes;
use util\Money;
use util\Currency;

class Monitoring {
 
  #[@get('/monitoring/status')]
  public function status() {
    return Response::ok()->type('text/plain')->body('OK');
  }

  #[@get('/monitoring/details')]
  public function startup() {
    return [
      'startup' => new Date('2018-06-02 14:12:11+0200'),
      'core'    => new Bytes('XP9'),
      'author'  => new Person(1549, 'Timm'),
      'cost'    => new Money(3.50, Currency::$EUR)
    ];
  }
}