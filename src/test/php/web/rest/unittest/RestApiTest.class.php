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
   * @param  int $status
   * @param  string $mime
   * @param  string $body
   * @param  web.Response $res
   * @throws unittest.AssertionFailedError
   * @return void
   */
  private function assertPayload($status, $mime, $body, $res) {
    $bytes= $res->output()->bytes();
    $this->assertEquals(
      ['status' => $status, 'mime' => $mime, 'body' => dechex(strlen($body))."\r\n".$body."\r\n0\r\n\r\n"],
      ['status' => $res->status(), 'mime' => $res->headers()['Content-Type'], 'body' => substr($bytes, strpos($bytes, "\r\n\r\n") + 4)]
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

    $this->assertPayload(200, 'application/json', '{"1549":{"id":1549,"name":"Timm"},"6100":{"id":6100,"name":"Test"}}', $res);
  }

  #[@test]
  public function find_user_returns_json() {
    $req= new Request(new TestInput('GET', '/users/1549'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(200, 'application/json', '{"id":1549,"name":"Timm"}', $res);
  }

  #[@test]
  public function exception_raised() {
    $req= new Request(new TestInput('GET', '/users/not.a.user'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(500, 'application/json', '{"status":500,"message":"Undefined index: not.a.user"}', $res);
  }

  #[@test]
  public function create_user_returns_created() {
    $body= '{"name":"New"}';
    $headers= ['Content-Type' => 'application/json', 'Content-Length' => strlen($body)];

    $req= new Request(new TestInput('POST', '/users', $headers, $body));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(201, 'application/json', '{"id":6101,"name":"New"}', $res);
  }

  #[@test]
  public function get_user_avatar_streams_image() {
    $req= new Request(new TestInput('GET', '/users/1549/avatar'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(200, 'image/png', 'PNG...', $res);
  }
}