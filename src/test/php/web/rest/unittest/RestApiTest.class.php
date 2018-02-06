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
  public function exception_raised_from_find_user_rendered_as_internal_server_error() {
    $req= new Request(new TestInput('GET', '/users/0'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(500, 'application/json', '{"status":500,"message":"Undefined offset: 0"}', $res);
  }

  #[@test, @ignore('Not yet implemented')]
  public function type_errors_for_arguments_rendered_as_bad_request() {
    $req= new Request(new TestInput('GET', '/users/not.an.int'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(400, 'application/json', '{"status":400,"message":"Expected integer for argument $id, have string"}', $res);
  }

  #[@test]
  public function missing_arguments_rendered_as_bad_request() {
    $req= new Request(new TestInput('POST', '/users'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(400, 'application/json', '{"status":400,"message":"Expecting a request body, none transmitted"}', $res);
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

  #[@test]
  public function change_users_avatar() {
    $body= 'PNG...';
    $headers= ['Content-Type' => 'image/png', 'Content-Length' => strlen($body)];

    $req= new Request(new TestInput('PUT', '/users/1549/avatar', $headers, $body));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertEquals(204, $res->status());
  }

  #[@test]
  public function not_found() {
    $req= new Request(new TestInput('GET', '/users/not.a.user/avatar'));
    $res= new Response(new TestOutput());

    (new RestApi(new Users()))->handle($req, $res);

    $this->assertPayload(404, 'application/json', '{"status":404,"message":"No such user #not.a.user"}', $res);
  }

  #[@test]
  public function plain_output() {
    $req= new Request(new TestInput('GET', '/monitoring/status'));
    $res= new Response(new TestOutput());

    (new RestApi(new Monitoring()))->handle($req, $res);

    $this->assertEquals(
      "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\nContent-Length: 2\r\n\r\nOK",
      $res->output()->bytes()
    );
  }

  #[@test]
  public function date_objects_are_serialized() {
    $req= new Request(new TestInput('GET', '/monitoring/details'));
    $res= new Response(new TestOutput());

    (new RestApi(new Monitoring()))->handle($req, $res);

    $details= '{'.
      '"startup":"2018-06-02T14:12:11+0200",'.
      '"core":"XP9",'.
      '"author":{"id":1549,"name":"Timm"},'.
      '"cost":{"amount":"3.5","currency":"EUR"}'.
    '}';
    $this->assertPayload(200, 'application/json', $details, $res);
  }
}