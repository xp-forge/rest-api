<?php namespace web\rest\unittest;

use lang\IllegalAccessException;
use unittest\TestCase;
use web\rest\Response;

class ResponseTest extends TestCase {
  const URI = 'http://example.com/';

  #[@test]
  public function ok() {
    $this->assertEquals(
      ['status' => 200, 'headers' => [], 'body' => null],
      Response::ok()->export()
    );
  }

  #[@test]
  public function created() {
    $this->assertEquals(
      ['status' => 201, 'headers' => [], 'body' => null],
      Response::created()->export()
    );
  }

  #[@test]
  public function created_with_location() {
    $this->assertEquals(
      ['status' => 201, 'headers' => ['Location' => self::URI], 'body' => null],
      Response::created(self::URI)->export()
    );
  }

  #[@test]
  public function no_content() {
    $this->assertEquals(
      ['status' => 204, 'headers' => [], 'body' => null],
      Response::noContent()->export()
    );
  }

  #[@test]
  public function see() {
    $this->assertEquals(
      ['status' => 302, 'headers' => ['Location' => self::URI], 'body' => null],
      Response::see(self::URI)->export()
    );
  }

  #[@test]
  public function not_modified() {
    $this->assertEquals(
      ['status' => 304, 'headers' => [], 'body' => null],
      Response::notModified()->export()
    );
  }

  #[@test]
  public function not_found() {
    $this->assertEquals(
      ['status' => 404, 'headers' => [], 'body' => null],
      Response::notFound()->export()
    );
  }

  #[@test]
  public function not_found_with_message() {
    $this->assertEquals(
      ['status' => 404, 'headers' => [], 'body' => ['error' => ['status' => 404, 'message' => 'No such user #0']]],
      Response::notFound('No such user #0')->export()
    );
  }

  #[@test]
  public function not_acceptable() {
    $this->assertEquals(
      ['status' => 406, 'headers' => [], 'body' => null],
      Response::notAcceptable()->export()
    );
  }

  #[@test]
  public function not_acceptable_with_message() {
    $this->assertEquals(
      ['status' => 406, 'headers' => [], 'body' => ['error' => ['status' => 406, 'message' => 'Missing argument "user"']]],
      Response::notAcceptable('Missing argument "user"')->export()
    );
  }

  #[@test]
  public function error() {
    $this->assertEquals(
      ['status' => 500, 'headers' => [], 'body' => null],
      Response::error()->export()
    );
  }

  #[@test]
  public function error_with_status() {
    $this->assertEquals(
      ['status' => 503, 'headers' => [], 'body' => null],
      Response::error(503)->export()
    );
  }

  #[@test]
  public function error_with_status_and_message() {
    $this->assertEquals(
      ['status' => 503, 'headers' => [], 'body' => ['error' => ['status' => 503, 'message' => 'Database error']]],
      Response::error(503, 'Database error')->export()
    );
  }

  #[@test]
  public function error_with_status_and_exception() {
    $this->assertEquals(
      ['status' => 403, 'headers' => [], 'body' => ['error' => ['status' => 403, 'message' => 'Not allowed']]],
      Response::error(403, new IllegalAccessException('Not allowed'))->export()
    );
  }

  #[@test]
  public function status() {
    $this->assertEquals(
      ['status' => 402, 'headers' => [], 'body' => null],
      Response::status(402)->export()
    );
  }

  #[@test]
  public function type() {
    $this->assertEquals(
      ['status' => 200, 'headers' => ['Content-Type' => 'text/plain'], 'body' => null],
      Response::ok()->type('text/plain')->export()
    );
  }

  #[@test]
  public function header() {
    $this->assertEquals(
      ['status' => 200, 'headers' => ['Age' => 12], 'body' => null],
      Response::ok()->header('Age', 12)->export()
    );
  }

  #[@test]
  public function multiple_headers() {
    $this->assertEquals(
      ['status' => 200, 'headers' => ['Age' => 12, 'Content-Language' => 'de'], 'body' => null],
      Response::ok()->header('Age', 12)->header('Content-Language', 'de')->export()
    );
  }

  #[@test]
  public function entity() {
    $this->assertEquals(
      ['status' => 200, 'headers' => [], 'body' => ['value' => [1, 2, 3]]],
      Response::ok()->entity([1, 2, 3])->export()
    );
  }

  #[@test]
  public function body() {
    $this->assertEquals(
      ['status' => 200, 'headers' => [], 'body' => ['bytes' => 'Test']],
      Response::ok()->body('Test')->export()
    );
  }
}