<?php namespace web\rest;

/** @test web.rest.unittest.ConversionsTest */
interface Conversion {

  /**
   * Converts a given input value.
   *
   * @param  var $input
   * @return var
   */
  public function convert($input);
}