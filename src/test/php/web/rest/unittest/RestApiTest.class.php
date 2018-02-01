<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\RestApi;

class RestApiTest extends TestCase {

  #[@test]
  public function can_create() {
    new RestApi(new Users());
  }
}