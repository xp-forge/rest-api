<?php namespace web\rest\unittest;

use test\{Assert, Test, Values};
use web\rest\{RestApi, Get, Param, ListWith};

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
}