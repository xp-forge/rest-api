<?php namespace web\rest\unittest;

use web\rest\Response;
use util\Date;
use util\Bytes;
use util\Money;
use util\Currency;

class Monitoring {
  private $startup, $responsible;

  /** Creates a new monitoring resource */
  public function __construct() {
    $this->startup= new Date('2018-06-02 14:12:11+0200');
    $this->responsible= new Person(1549, 'Timm');
  }
 
  #[@get('/monitoring/status')]
  public function status() {
    return Response::ok()->type('text/plain')->body('OK');
  }

  #[@get('/monitoring/details')]
  public function startup() {
    return [
      'startup'     => $this->startup,
      'core'        => new Bytes('XP9'),
      'responsible' => $this->responsible,
      'cost'        => new Money(3.50, Currency::$EUR)
    ];
  }

  #[@put('/monitoring/startup'), @$startup: entity]
  public function reset(Date $startup) {
    $this->startup= $startup;
    return $this->startup;
  }

  #[@put('/monitoring/responsible'), @$responsible: entity]
  public function change(Person $responsible) {
    $this->responsible= $responsible;
    return Response::ok()->entity($this->responsible);
  }
}