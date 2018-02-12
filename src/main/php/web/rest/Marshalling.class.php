<?php namespace web\rest;

use util\Date;
use util\Money;
use util\Currency;
use lang\XPClass;
use lang\ArrayType;
use lang\MapType;
use lang\Type;
use lang\Enum;

/**
 * Takes care of converting objects from and to maps
 *
 * @test  xp://web.rest.unittest.MarshallingTest
 */
class Marshalling {

  /**
   * Applies unmarshal() to values inside an iterable
   *
   * @param  iterable $in
   * @param  lang.Type $type
   * @return iterable
   */
  private function iterable($in, $type) {
    foreach ($in as $key => $value) {
      yield $key => $this->unmarshal($value, $type);
    }
  }

  /**
   * Unmarshals a value. Handles util.Date and util.Money instances specially,
   * creates instances if the type has a single-argument constructor; treats
   * other types in a generic way, iterating over their instance fields.
   *
   * @param  var $value
   * @param  lang.Type $type
   * @return var
   */
  public function unmarshal($value, $type) {
    if ($type instanceof XPClass) {
      if ($type->isInterface()) {
        return $type->cast($value);
      } else if ($type->isEnum()) {
        return Enum::valueOf($type, $value);
      } else if ($type->isAssignableFrom(Date::class)) {
        return new Date($value);
      } else if ($type->isAssignableFrom(Money::class)) {
        return new Money($value['amount'], Currency::getInstance($value['currency']));
      } else if (1 === $type->getConstructor()->numParameters()) {
        return $type->newInstance($value);
      }

      $n= $type->literal();
      $r= unserialize('O:'.strlen($n).':"'.$n.'":0:{}');
      foreach ($type->getFields() as $field) {
        $m= $field->getModifiers();
        if ($m & MODIFIER_STATIC) continue;

        $n= $field->getName();
        if ($m & MODIFIER_PUBLIC) {
          $field->set($r, $this->unmarshal($value[$n], $field->getType()));
        } else if ($type->hasMethod($set= 'set'.ucfirst($n))) {
          $method= $type->getMethod($set);
          $method->invoke($r, $this->unmarshal($value[$n], $method->getParameter(0)->getType()));
        } else {
          $field->setAccessible(true)->set($r, $this->unmarshal($value[$n], $field->getType()));
        }
      }
      return $r;
    } else if ($type instanceof ArrayType || $type instanceof MapType) {
      $t= $type->componentType();
      $r= [];
      foreach ($value as $k => $v) {
        $r[$k]= $this->unmarshal($v, $t);
      }
      return $r;
    } else if ($type === Type::$ARRAY) {
      $t= Type::$VAR;
      $r= [];
      foreach ($value as $k => $v) {
        $r[$k]= $this->unmarshal($v, $t);
      }
      return $r;
    } else if ($type === Type::$ITERABLE) {
      return $this->iterable($value, Type::$VAR);
    } else {
      return $type->cast($value);
    }
  }

  /**
   * Applies marshal() to values inside a generator
   *
   * @param  iterable $in
   * @return iterable
   */
  private function generator($in) {
    foreach ($in as $key => $value) {
      yield $key => $this->marshal($value);
    }
  }

  /**
   * Marshals a value. Handles util.Date and util.Money instances specially,
   * converts objects with a `__toString` method and handles other objects
   * in a generic way, iterating over their instance fields.
   * 
   * @param  var $value
   * @return var
   */
  public function marshal($value) {
    if ($value instanceof Date) {
      return $value->toString(DATE_ISO8601);
    } else if ($value instanceof Money) {
      return ['amount' => $value->amount(), 'currency' => $value->currency()->toString()];
    } else if ($value instanceof Enum) {
      return $value->name();
    } else if ($value instanceof \Traversable) {
      return $this->generator($value);
    } else if (is_object($value)) {
      if (method_exists($value, '__toString')) return $value->__toString();

      $r= [];
      $type= typeof($value);
      foreach ($type->getFields() as $field) {
        $m= $field->getModifiers();
        if ($m & MODIFIER_STATIC) continue;

        $n= $field->getName();
        if ($m & MODIFIER_PUBLIC) {
          $r[$n]= $field->get($value);
        } else if ($type->hasMethod($n)) {
          $r[$n]= $type->getMethod($n)->invoke($value, []);
        } else if ($type->hasMethod($get= 'get'.ucfirst($n))) {
          $r[$n]= $type->getMethod($get)->invoke($value, []);
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