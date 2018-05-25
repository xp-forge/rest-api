<?php namespace web\rest\unittest;

class PersonWithoutConstructor {

  /** @var int */
  public $id;

  /** @var string */
  public $name;

  /**
   * @param int $id
   * @return PersonWithoutConstructor
   */
  public function setId($id) {
    $this->id= $id;
    return $this;
  }

  /**
   * @param string $name
   * @return PersonWithoutConstructor
   */
  public function setName($name) {
    $this->name= $name;
    return $this;
  }

}