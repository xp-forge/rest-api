<?php namespace web\rest\unittest\api;

use util\{Currency, Date, Money};
use web\Request;
use web\rest\unittest\Person;
use web\rest\{Response, Resource, Entity, Get, Put};

#[Resource]
class Monitoring {
  private $startup, $responsible;

  /** Creates a new monitoring resource */
  public function __construct() {
    $this->startup= new Date('2018-06-02 14:12:11+0200');
    $this->responsible= new Person(1549, 'Timm');
  }
 
  #[Get('/monitoring/systems')]
  public function systems(Request $req) {
    return Response::ok()->entity(['page' => $req->param('page')]);
  }

  #[Get('/monitoring/status')]
  public function status() {
    return Response::ok()->type('text/plain')->body('OK');
  }

  #[Get('/monitoring/details')]
  public function startup() {
    return new Details([
      'startup'     => $this->startup,
      'core'        => 'XP9',
      'responsible' => $this->responsible,
      'cost'        => new Money(3.50, Currency::$EUR)
    ]);
  }

  #[Put('/monitoring/startup')]
  public function reset(
    #[Entity]
    Date $startup
  ) {
    $this->startup= $startup;
    return Response::ok()->entity($this->startup);
  }

  #[Put('/monitoring/responsible')]
  public function change(
    #[Entity]
    Person $responsible
  ) {
    $this->responsible= $responsible;
    return Response::ok()->entity($this->responsible);
  }
}