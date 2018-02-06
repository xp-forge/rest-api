<?php namespace web\rest;

use util\Date;
use util\Money;

class Marshalling {

  /**
   * Marshals a value. Handles util.Date and util.Money instances specially,
   * converts objects with a `__toString` method and handles other objects
   * in a generic way, iterating over their instance members.
   * 
   * @param  var $value
   * @return var
   */
  public function marshal($value) {
    if ($value instanceof Date) {
      return $value->toString(DATE_ISO8601);
    } else if ($value instanceof Money) {
      return ['amount' => $value->amount(), 'currency' => $value->currency()->toString()];
    } else if ($value instanceof \Generator) {
      return $value;
    } else if (is_object($value)) {
      if (method_exists($value, '__toString')) return $value->__toString();

      $r= [];
      $t= typeof($value);
      foreach ($t->getFields() as $field) {
        $m= $field->getModifiers();
        if ($m & MODIFIER_STATIC) continue;

        $n= $field->getName();
        if ($m & MODIFIER_PUBLIC) {
          $r[$n]= $field->get($value);
        } else if ($t->hasMethod($n)) {
          $r[$n]= $t->getMethod($n)->invoke($value, []);
        } else if ($t->hasMethod($get= 'get'.ucfirst($n))) {
          $r[$n]= $t->getMethod($get)->invoke($value, []);
        } else {
          $r[$n]= $field->setAccessible(true)->get($value);
        }
      }
      return $r;
    } else if (is_array($value)) {
      $r= [];
      foreach ($value as $k => $v) {
        $r[$k]= $this->marshal($v);
      }
      return $r;
    } else {
      return $value;
    }
  }
}