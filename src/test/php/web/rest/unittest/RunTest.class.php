<?php namespace web\rest\unittest;

use test\Assert;
use web\io\{TestInput, TestOutput};
use web\{Request, Response};

abstract class RunTest {
  const JSON = 'application/json; charset=utf-8';

  /**
   * Assertion helper - tests HTTP payload. Assumes chunked transfer-encoding.
   *
   * @param  int $status
   * @param  string $mime
   * @param  ?string $body
   * @param  web.Response $res
   * @throws unittest.AssertionFailedError
   * @return void
   */
  protected function assertPayload($status, $mime, $body, $res) {
    $bytes= $res->output()->bytes();
    $response= substr($bytes, strpos($bytes, "\r\n\r\n") + 4);
    $chunked= null === $body ? '' : dechex(strlen($body))."\r\n".$body."\r\n0\r\n\r\n";
    Assert::equals(
      ['status' => $status, 'mime' => $mime, 'body' => $chunked],
      ['status' => $res->status(), 'mime' => $res->headers()['Content-Type'], 'body' => $response]
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
   * @param  var $user
   * @return web.Response
   */
  protected function run($api, $method= 'GET', $uri= '/', $headers= [], $body= '', $user= null) {
    $req= new Request(new TestInput($method, $uri, $headers, $body));
    $res= new Response(new TestOutput());

    foreach ($api->handle($req->pass('user', $user), $res) ?? [] as $_) { }
    return $res;
  }
}