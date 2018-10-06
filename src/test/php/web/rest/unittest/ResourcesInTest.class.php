<?php namespace web\rest\unittest;

use lang\reflect\Package;
use unittest\TestCase;
use web\rest\ResourcesIn;

class ResourcesInTest extends TestCase {

  #[@test]
  public function using_package_name() {
    $r= new ResourcesIn('web.rest.unittest.api');
    $this->assertNotEquals(null, $r->target('get', '/monitoring/status'));
  }

  #[@test]
  public function using_package_instance() {
    $r= new ResourcesIn(Package::forName('web.rest.unittest.api'));
    $this->assertNotEquals(null, $r->target('get', '/monitoring/status'));
  }

  #[@test]
  public function supply_creation_function() {
    $classes= [];
    $r= new ResourcesIn('web.rest.unittest.api', function($class) use(&$classes) {
      $classes[]= $class->getName();
      return $class->newInstance();
    });
    sort($classes);
    $this->assertEquals(['web.rest.unittest.api.Monitoring', 'web.rest.unittest.api.Users'], $classes);
  }
}