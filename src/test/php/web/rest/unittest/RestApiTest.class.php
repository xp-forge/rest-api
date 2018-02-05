<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\RestApi;
use web\Request;
use web\Response;
use web\io\TestInput;
use web\io\TestOutput;

class RestApiTest extends TestCase {

  /**
   * Assertion helper - tests HTTP payload. Assumes chunked transfer-encoding.
   *
   * @param  string $expected
   * @param  string $bytes
   * @throws unittest.AssertionFailedError
   * @return void
   */
  private function assertPayload($expected, $bytes) {
    $this->assertEquals(
      dechex(strlen($expected))."\r\n".$expected."\r\n0\r\n\r\n",
      substr($bytes, strpos($bytes, "\r\n\r\n") + 4)
    );
  }

  #[@test]
  public function can_create() {
    new RestApi(new Users());
  }

  #[@test]
  public function list_users_returns_json() {
    $req= new Request(new TestInput('GET', '/users'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(
      '{"1549":{"id":1549,"name":"Timm"},"6100":{"id":6100,"name":"Test"}}',
      $res->output()->bytes()
    );
  }

  #[@test]
  public function find_user_returns_json() {
    $req= new Request(new TestInput('GET', '/users/1549'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(
      '{"id":1549,"name":"Timm"}',
      $res->output()->bytes()
    );
  }
}