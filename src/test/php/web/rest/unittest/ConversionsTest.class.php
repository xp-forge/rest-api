<?php namespace web\rest\unittest;

use test\{Assert, Test, Values};
use web\rest\{RestApi, Get, Param, ListWith, Matrix};

class ConversionsTest extends RunTest {

  #[Test, Values([['', '[]'], ['select=one', '["one"]'], ['select=one,two', '["one","two"]'], ['select[]=one', '["one"]'], ['select[]=one&select[]=two', '["one","two"]']])]
  public function list_with($query, $output) {
    $api= new class() {

      #[Get('/')]
      public function test(
        #[Param, ListWith(',')]
        array $select= []
      ) {
        return $select;
      }
    };

    $this->assertPayload(
      200,
      self::JSON,
      $output,
      $this->run(new RestApi($api), 'GET', '/?'.$query)
    );
  }

  #[Test, Values([['uid=0', '{"uid":"0"}'], ['variants=a%26b;owned=true;colors=green,blue', '{"variants":"a&b","owned":"true","colors":["green","blue"]}']])]
  public function matrix_parameter($path, $output) {
    $api= new class() {

      #[Get('/{filter}')]
      public function test(
        #[Matrix]
        array $filter= []
      ) {
        return $filter;
      }
    };

    $this->assertPayload(
      200,
      self::JSON,
      $output,
      $this->run(new RestApi($api), 'GET', '/'.$path)
    );
  }

  #[Test]
  public function conversions_can_be_combined_with_type_hinting() {
    $api= new class() {

      #[Get('/{filter}/authors')]
      public function test(
        #[Matrix]
        Filters $filter
      ) {
        return $filter;
      }
    };

    $this->assertPayload(
      200,
      self::JSON,
      '{"status":"COMPLETED","orgunits":["a","b"]}',
      $this->run(new RestApi($api), 'GET', '/status=COMPLETED;orgunits=a,b/authors')
    );
  }

  #[Test]
  public function branches() {
    $api= new class() {

      #[Get('/compare/{branches}')]
      public function test(
        #[Branches]
        array $branches
      ) {
        return $branches;
      }
    };

    $this->assertPayload(
      200,
      self::JSON,
      '["main","feature"]',
      $this->run(new RestApi($api), 'GET', '/compare/main...feature')
    );
  }

  #[Test]
  public function exceptions_raised_during_conversion_yield_bad_request() {
    $api= new class() {

      #[Get('/compare/{branches}')]
      public function test(
        #[Branches]
        array $branches
      ) {
        return $branches;
      }
    };

    $this->assertPayload(
      400,
      self::JSON,
      '{"status":400,"message":"Malformed input \"main\""}',
      $this->run(new RestApi($api), 'GET', '/compare/main')
    );
  }
}