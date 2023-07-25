<?php namespace web\rest;

use lang\reflection\Package;

/**
 * Creates routing based on resource classes in a given package
 *
 * @test  web.rest.unittest.ResourcesInTest
 */
class ResourcesIn extends Delegates {

  /**
   * Creates this delegates instance
   *
   * @param  lang.reflection.Package|string $package
   * @param  function(lang.XPClass): object $new Optional function to create instances
   */
  public function __construct($package, $new= null) {
    $p= $package instanceof Package ? $package : new Package($package);
    foreach ($p->types() as $type) {
      if ($resource= $type->annotation(Resource::class)) {
        $this->with($new ? $new($type->class()) : $type->newInstance(), (string)$resource->argument(0));
      }
    }
    uksort($this->patterns, function($a, $b) { return strlen($b) - strlen($a); });
  }
}