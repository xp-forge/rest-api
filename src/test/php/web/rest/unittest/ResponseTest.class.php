<?php namespace web\rest\unittest;

use lang\IllegalAccessException;
use test\{Assert, Test, TestCase};
use web\rest\Response;

class ResponseTest {
  const URI = 'http://example.com/';

  #[Test]
  public function ok() {
    Assert::equals(
      ['status' => 200, 'headers' => [], 'body' => null],
      Response::ok()->export()
    );
  }

  #[Test]
  public function created() {
    Assert::equals(
      ['status' => 201, 'headers' => [], 'body' => null],
      Response::created()->export()
    );
  }

  #[Test]
  public function created_with_location() {
    Assert::equals(
      ['status' => 201, 'headers' => ['Location' => self::URI], 'body' => null],
      Response::created(self::URI)->export()
    );
  }

  #[Test]
  public function created_with_location_and_arguments() {
    Assert::equals(
      ['status' => 201, 'headers' => ['Location' => '/users/~friebe/avatars/1'], 'body' => null],
      Response::created('/users/{user}/avatars/{id}', '~friebe', 1)->export()
    );
  }

  #[Test]
  public function accepted() {
    Assert::equals(
      ['status' => 201, 'headers' => [], 'body' => null],
      Response::accepted()->export()
    );
  }

  #[Test]
  public function no_content() {
    Assert::equals(
      ['status' => 204, 'headers' => [], 'body' => null],
      Response::noContent()->export()
    );
  }

  #[Test]
  public function see() {
    Assert::equals(
      ['status' => 302, 'headers' => ['Location' => self::URI], 'body' => null],
      Response::see(self::URI)->export()
    );
  }

  #[Test]
  public function see_with_arguments() {
    Assert::equals(
      ['status' => 302, 'headers' => ['Location' => '/users/~friebe/avatars/1'], 'body' => null],
      Response::see('/users/{user}/avatars/{id}', '~friebe', 1)->export()
    );
  }

  #[Test]
  public function not_modified() {
    Assert::equals(
      ['status' => 304, 'headers' => [], 'body' => null],
      Response::notModified()->export()
    );
  }

  #[Test]
  public function not_found() {
    Assert::equals(
      ['status' => 404, 'headers' => [], 'body' => null],
      Response::notFound()->export()
    );
  }

  #[Test]
  public function not_found_with_message() {
    Assert::equals(
      ['status' => 404, 'headers' => [], 'body' => ['error' => ['status' => 404, 'message' => 'No such user #0']]],
      Response::notFound('No such user #0')->export()
    );
  }

  #[Test]
  public function not_acceptable() {
    Assert::equals(
      ['status' => 406, 'headers' => [], 'body' => null],
      Response::notAcceptable()->export()
    );
  }

  #[Test]
  public function not_acceptable_with_message() {
    Assert::equals(
      ['status' => 406, 'headers' => [], 'body' => ['error' => ['status' => 406, 'message' => 'Missing argument "user"']]],
      Response::notAcceptable('Missing argument "user"')->export()
    );
  }

  #[Test]
  public function error() {
    Assert::equals(
      ['status' => 500, 'headers' => [], 'body' => null],
      Response::error()->export()
    );
  }

  #[Test]
  public function error_with_status() {
    Assert::equals(
      ['status' => 503, 'headers' => [], 'body' => null],
      Response::error(503)->export()
    );
  }

  #[Test]
  public function error_with_status_and_message() {
    Assert::equals(
      ['status' => 503, 'headers' => [], 'body' => ['error' => ['status' => 503, 'message' => 'Database error']]],
      Response::error(503, 'Database error')->export()
    );
  }

  #[Test]
  public function error_with_status_and_exception() {
    Assert::equals(
      ['status' => 403, 'headers' => [], 'body' => ['error' => ['status' => 403, 'message' => 'Not allowed']]],
      Response::error(403, new IllegalAccessException('Not allowed'))->export()
    );
  }

  #[Test]
  public function status() {
    Assert::equals(
      ['status' => 402, 'headers' => [], 'body' => null],
      Response::status(402)->export()
    );
  }

  #[Test]
  public function type() {
    Assert::equals(
      ['status' => 200, 'headers' => ['Content-Type' => 'text/plain'], 'body' => null],
      Response::ok()->type('text/plain')->export()
    );
  }

  #[Test]
  public function header() {
    Assert::equals(
      ['status' => 200, 'headers' => ['Age' => 12], 'body' => null],
      Response::ok()->header('Age', 12)->export()
    );
  }

  #[Test]
  public function multiple_headers() {
    Assert::equals(
      ['status' => 200, 'headers' => ['Age' => 12, 'Content-Language' => 'de'], 'body' => null],
      Response::ok()->header('Age', 12)->header('Content-Language', 'de')->export()
    );
  }

  #[Test]
  public function entity() {
    Assert::equals(
      ['status' => 200, 'headers' => [], 'body' => ['value' => [1, 2, 3]]],
      Response::ok()->entity([1, 2, 3])->export()
    );
  }

  #[Test]
  public function body() {
    Assert::equals(
      ['status' => 200, 'headers' => [], 'body' => ['bytes' => 'Test']],
      Response::ok()->body('Test')->export()
    );
  }
}