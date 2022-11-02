<?php namespace web\rest\unittest;

use unittest\{Assert, Expect, Ignore, Test, Values};
use web\Error;
use web\rest\format\Json;
use web\rest\unittest\api\{Monitoring, Users};
use web\rest\{MethodsIn, ResourcesIn, RestApi};

class RestApiTest extends RunTest {

  #[Test]
  public function can_create() {
    new RestApi(new Users());
  }

  #[Test]
  public function can_create_with_methods_delegates() {
    new RestApi(new MethodsIn(new Users()));
  }

  #[Test]
  public function can_create_with_classes_delegates() {
    new RestApi(new ResourcesIn('web.rest.unittest.api'));
  }

  #[Test]
  public function list_users_returns_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users');
    $this->assertPayload(
      200,
      self::JSON,
      '{"1549":{"id":1549,"handle":"thekid","name":"Timm"},"6100":{"id":6100,"handle":"test","name":"Test"}}',
      $res
    );
  }

  #[Test]
  public function returns_100_continue_if_expect_sent() {
    $payload= $this->run(new RestApi(new Users()), 'GET', '/users', ['Expect' => '100-continue'])
      ->output()
      ->bytes()
    ;
    Assert::equals('HTTP/1.1 100 Continue', substr($payload, 0, strpos($payload, "\r\n")));
  }

  #[Test]
  public function count_users_returns_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/count');
    $this->assertPayload(200, self::JSON, '2', $res);
  }

  #[Test]
  public function find_user_by_name_returns_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/@thekid');
    $this->assertPayload(200, self::JSON, '{"id":1549,"handle":"thekid","name":"Timm"}', $res);
  }

  #[Test]
  public function find_user_by_id_returns_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/1549');
    $this->assertPayload(200, self::JSON, '{"id":1549,"handle":"thekid","name":"Timm"}', $res);
  }

  #[Test]
  public function exception_raised_from_find_user_rendered_as_internal_server_error() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/0');
    $this->assertPayload(500, self::JSON, '{"status":500,"message":"No such user #0"}', $res);
  }

  #[Test]
  public function error_raised_from_delete_user_renderd_with_statuscode() {
    $res= $this->run(new RestApi(new Users()), 'DELETE', '/users/0');
    $this->assertPayload(402, self::JSON, '{"status":402,"message":"Payment Required"}', $res);
  }

  #[Test, Ignore('Not yet implemented')]
  public function type_errors_for_arguments_rendered_as_bad_request() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/not.an.int');
    $this->assertPayload(400, self::JSON, '{"status":400,"message":"Expected integer for argument $id, have string"}', $res);
  }

  #[Test]
  public function missing_body_rendered_as_bad_request() {
    $res= $this->run(new RestApi(new Users()), 'POST', '/users');
    $this->assertPayload(400, self::JSON, '{"status":400,"message":"Expecting a request body, none transmitted"}', $res);
  }

  #[Test, Values([['application/json', '{"name":"New"}'], ['application/x-www-form-urlencoded', 'name=New']])]
  public function create_user_returns_created($type, $body) {
    $headers= ['Content-Type' => $type, 'Content-Length' => strlen($body)];

    $res= $this->run(new RestApi(new Users()), 'POST', '/users', $headers, $body);
    $this->assertPayload(201, self::JSON, '{"id":6101,"name":"New"}', $res);
  }

  #[Test]
  public function get_user_avatar_streams_image() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/1549/avatar');
    $this->assertPayload(200, 'image/png', 'PNG...', $res);
  }

  #[Test]
  public function change_users_avatar() {
    $body= 'PNG...';
    $headers= ['Content-Type' => 'image/png', 'Content-Length' => strlen($body)];

    $res= $this->run(new RestApi(new Users()), 'PUT', '/users/1549/avatar', $headers, $body);
    Assert::equals(204, $res->status());
  }

  #[Test]
  public function not_found() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/not.a.user/avatar');
    $this->assertPayload(404, self::JSON, '{"status":404,"message":"No such user #not.a.user"}', $res);
  }

  #[Test, Values(['/monitoring/status', '/monitoring/details'])]
  public function default_headers_for($resource) {
    $res= $this->run(new RestApi(new Monitoring()), 'GET', $resource);
    Assert::equals('nosniff', $res->headers()['X-Content-Type-Options']);
    Assert::equals('no-cache', $res->headers()['Cache-Control']);
  }

  #[Test]
  public function plain_output() {
    $res= $this->run(new RestApi(new Monitoring()), 'GET', '/monitoring/status');
    Assert::equals(
      "HTTP/1.1 200 OK\r\n".
      "X-Content-Type-Options: nosniff\r\n".
      "Cache-Control: no-cache\r\n".
      "Content-Type: text/plain\r\n".
      "Content-Length: 2\r\n".
      "\r\n".
      "OK",
      $res->output()->bytes()
    );
  }

  #[Test]
  public function objects_are_marshalled() {
    $res= $this->run(new RestApi(new Monitoring()), 'GET', '/monitoring/details');
    $details= '{"values":{'.
      '"startup":"2018-06-02T14:12:11+0200",'.
      '"core":"XP9",'.
      '"responsible":{"id":1549,"name":"Timm"},'.
      '"cost":{"amount":"3.5","currency":"EUR"}'.
    '}}';
    $this->assertPayload(200, self::JSON, $details, $res);
  }

  #[Test]
  public function date_is_unmarshalled() {
    $body= '"2018-06-02T11:55:11+0200"';
    $headers= ['Content-Type' => 'application/json', 'Content-Length' => strlen($body)];

    $res= $this->run(new RestApi(new Monitoring()), 'PUT', '/monitoring/startup', $headers, $body);
    $this->assertPayload(200, self::JSON, $body, $res);
  }

  #[Test]
  public function person_is_unmarshalled() {
    $body= '{"id":1549,"name":"Timm"}';
    $headers= ['Content-Type' => 'application/json', 'Content-Length' => strlen($body)];

    $res= $this->run(new RestApi(new Monitoring()), 'PUT', '/monitoring/responsible', $headers, $body);
    $this->assertPayload(200, self::JSON, $body, $res);
  }

  #[Test]
  public function rest_api_base() {
    $res= $this->run(new RestApi(new Users(), '/api/1.0'), 'GET', '/api/1.0/users');
    $this->assertPayload(
      200,
      self::JSON,
      '{"1549":{"id":1549,"handle":"thekid","name":"Timm"},"6100":{"id":6100,"handle":"test","name":"Test"}}',
      $res
    );
  }

  #[Test]
  public function typed_request_instances_can_be_injected() {
    $res= $this->run(new RestApi(new Monitoring()), 'GET', '/monitoring/systems?page=3');
    $this->assertPayload(200, self::JSON, '{"page":"3"}', $res);
  }

  #[Test]
  public function accept_all_defaults_to_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/1549', ['Accept' => '*/*']);
    $this->assertPayload(200, self::JSON, '{"id":1549,"handle":"thekid","name":"Timm"}', $res);
  }

  #[Test]
  public function accept_json_returns_json() {
    $res= $this->run(new RestApi(new Users()), 'GET', '/users/1549', ['Accept' => 'application/json']);
    $this->assertPayload(200, self::JSON, '{"id":1549,"handle":"thekid","name":"Timm"}', $res);
  }

  #[Test, Expect(['class' => Error::class, 'withMessage' => 'Unsupported mime type']), Values(['text/html, application/xhtml+xml, application/xml; q=0.9', 'application/xml', 'text/xml'])]
  public function does_not_accept($mime) {
    $this->run(new RestApi(new Users()), 'GET', '/users/1549', ['Accept' => $mime]);
  }
}