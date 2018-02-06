<?php namespace web\rest\unittest;

use web\rest\Response;

class Monitoring {
 
  #[@get('/monitoring/status')]
  public function monitoring() {
    return Response::ok()->type('text/plain')->body('OK');
  }
}