<?php namespace web\rest;

abstract class Conversion {

  /**
   * Converts a given input value.
   *
   * @param  var $input
   * @return var
   */
  public abstract function convert($input);
}