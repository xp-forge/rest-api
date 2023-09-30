<?php namespace web\rest\unittest;

use lang\{Generic, IllegalArgumentException};

#[Generic(self: 'T')]
class Range {
  private $begin, $end;

  /**
   * Creates a range from a given two-element array, casting if necessary.
   *
   * @throws lang.IllegalArgumentException
   * @throws lang.ClassCastException
   */
  public function __construct(array $range) {
    if (2 !== sizeof($range)) {
      throw new IllegalArgumentException('Given array must have 2 elements');
    }
    list($this->begin, $this->end)= array_map([$T, 'cast'], $range);
  }

  /** Returns begin of range */
  #[Generic(return: 'T')]
  public function begin() { return $this->begin; }

  /** Returns end of range */
  #[Generic(return: 'T')]
  public function end() { return $this->end; }
}
