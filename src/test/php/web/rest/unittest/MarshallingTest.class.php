<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\Marshalling;
use util\Date;
use util\Money;
use util\Currency;
use lang\Type;

class MarshallingTest extends TestCase {

  #[@test]
  public function can_create() {
    new Marshalling();
  }

  #[@test, @values([
  #  0, -1, 1,
  #  0.5, -1.5,
  #  null, true, false,
  #  '', 'Test',
  #  [[]], [[1, 2, 3]],
  #  [['key' => 'value']],
  #])]
  public function marshal($value) {
    $this->assertEquals($value, (new Marshalling())->marshal($value));
  }

  #[@test]
  public function marshal_date_uses_is8601() {
    $this->assertEquals(
      '2018-02-07T09:47:00+0100',
      (new Marshalling())->marshal(new Date('2018-02-07 09:47:00+0100'))
    );
  }

  #[@test]
  public function marshal_enum() {
    $this->assertEquals('EUR', (new Marshalling())->marshal(Currency::$EUR));
  }

  #[@test]
  public function marshal_money_uses_amount_and_currency() {
    $this->assertEquals(
      ['amount' => '3.5', 'currency' => 'EUR'],
      (new Marshalling())->marshal(new Money(3.50, Currency::$EUR))
    );
  }

  #[@test]
  public function marshal_person_value_object() {
    $this->assertEquals(
      ['id' => 6100, 'name' => 'Test'],
      (new Marshalling())->marshal(new Person(6100, 'Test'))
    );
  }

  #[@test]
  public function marshal_person_value_object_inside_map() {
    $this->assertEquals(
      ['person' => ['id' => 6100, 'name' => 'Test']],
      (new Marshalling())->marshal(['person' => new Person(6100, 'Test')])
    );
  }

  #[@test]
  public function marshal_generator() {
    $generator= function() { yield 1; yield 2; yield 3; };
    $this->assertEquals([1, 2, 3], iterator_to_array((new Marshalling())->marshal($generator())));
  }

  #[@test, @values([
  #  0, -1, 1,
  #  0.5, -1.5,
  #  null, true, false,
  #  '', 'Test',
  #  [[]], [[1, 2, 3]],
  #  [['key' => 'value']],
  #])]
  public function unmarshal($value) {
    $this->assertEquals($value, (new Marshalling())->unmarshal($value, Type::$VAR));
  }

  #[@test, @values([
  #  '2018-02-07T09:47:00+0100',
  #  '2018-02-07 09:47:00+0100',
  #  '07.02.2018 09:47:00+0100',
  #  '07.02.2018 09:47:00 Europe/Berlin',
  #])]
  public function unmarshal_date_accepts($format) {
    $this->assertEquals(
      new Date('2018-02-07 09:47:00+0100'),
      (new Marshalling())->unmarshal($format, Type::forName(Date::class))
    );
  }

  #[@test]
  public function unmarshal_enum() {
    $this->assertEquals(Currency::$EUR, (new Marshalling())->unmarshal('EUR', Type::forName(Currency::class)));
  }

  #[@test]
  public function unmarshal_money_uses_amount_and_currency() {
    $this->assertEquals(
      new Money(3.50, Currency::$EUR),
      (new Marshalling())->unmarshal(['amount' => '3.5', 'currency' => 'EUR'], Type::forName(Money::class))
    );
  }

  #[@test]
  public function unmarshal_person_value_object() {
    $this->assertEquals(
      new Person(6100, 'Test'),
      (new Marshalling())->unmarshal(['id' => 6100, 'name' => 'Test'], Type::forName(Person::class))
    );
  }

  #[@test]
  public function unmarshal_person_value_object_from_inside_map() {
    $this->assertEquals(
      ['person' => new Person(6100, 'Test')],
      (new Marshalling())->unmarshal(['person' => ['id' => 6100, 'name' => 'Test']], Type::forName('[:web.rest.unittest.Person]'))
    );
  }

  #[@test]
  public function unmarshal_iterable() {
    $this->assertEquals([1, 2, 3], iterator_to_array((new Marshalling())->unmarshal([1, 2, 3], Type::$ITERABLE)));
  }
}