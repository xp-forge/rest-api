<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\RestApi;
use web\Request;
use web\Response;
use web\io\TestInput;
use web\io\TestOutput;

class InvocationsTest extends TestCase {

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

  /**
   * Runs the handler
   *
   * @param  web.rest.RestApi $api
   * @param  string $method
   * @param  string $uri
   * @param  [:string] $headers
   * @param  string $body
   * @return web.Response
   */
  private function run($api, $method, $uri, $headers= [], $body= null) {
    $req= new Request(new TestInput($method, $uri, $headers, $body));
    $res= new Response(new TestOutput());

    $api->handle($req, $res);
    return $res;
  }

  #[@test]
  public function invoking_callable() {
    $invocations= function($delegate, $args) use(&$invoked) {
      $invoked= [$delegate->name(), $args];
      return $delegate->invoke($args);
    };

    $this->run((new RestApi(new Users()))->intercepting($invocations), 'GET', '/users/1549');
    $this->assertEquals(['web.rest.unittest.Users::findUser', ['1549']], $invoked);
  }
}