<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\RestApi;
use web\Request;
use web\Response;
use web\io\TestInput;
use web\io\TestOutput;

class RestApiTest extends TestCase {

  #[@test]
  public function can_create() {
    new RestApi(new Users());
  }

  #[@test]
  public function list_users() {
    $req= new Request(new TestInput('GET', '/users'));
    $res= new Response($out= new TestOutput());

    $fixture= new RestApi(new Users());
    $fixture->handle($req, $res);

    $this->assertEquals(
      "HTTP/1.1 200 OK\r\n".
      "Content-Type: application/json\r\n".
      "Transfer-Encoding: chunked\r\n\r\n".
      "43\r\n".
      "{\"1549\":{\"id\":1549,\"name\":\"Timm\"},\"6100\":{\"id\":6100,\"name\":\"Test\"}}\r\n".
      "0\r\n\r\n",
      $out->bytes()
    );
  }
}