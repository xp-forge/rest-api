<?php namespace web\rest;

use Generator;

/** @test web.rest.unittest.AsyncTest */
class Async {
  private $handler;

  /** Creates a new async from a given callable */
  public function __construct(callable $handler) {
    $this->handler= $handler;
  }

  /** Executes handler and returns awaitable */
  public function awaitable(): Generator {
    $r= ($this->handler)();
    return $r instanceof Generator ? $r : (function() use($r) { return $r; yield; })();
  }
}