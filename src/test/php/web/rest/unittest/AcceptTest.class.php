<?php namespace web\rest\unittest;

use unittest\TestCase;
use web\rest\Accept;

class AcceptTest extends TestCase {

  #[@test]
  public function can_create() {
    new Accept('*/*');
  }

  #[@test]
  public function all_simply_selects_first() {
    $this->assertEquals('text/html', (new Accept('*/*'))->match('text/html'));
  }

  #[@test]
  public function unmatched() {
    $this->assertNull((new Accept('application/json'))->match('text/html'));
  }

  #[@test]
  public function text_all_selects_first_text_type() {
    $this->assertEquals(
      'text/html',
      (new Accept('text/*'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function concrete_type_matched() {
    $this->assertEquals(
      'text/plain',
      (new Accept('text/plain'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[@test, @values([
  #  'text/*; q=0.8, text/plain; q=1.0',
  #  'text/plain; q=1.0, text/*; q=0.8',
  #  'text/plain; q=1.0, text/*',
  #  'text/plain, text/*'
  #])]
  public function selects_type_with_higher_q($header) {
    $this->assertEquals(
      'text/plain',
      (new Accept($header))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function wikipedia_example_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Accept('text/html; q=1.0, text/*; q=0.8, image/gif; q=0.6, image/jpeg; q=0.6, image/*; q=0.5, */*; q=0.1
'))->match(['text/plain', 'text/html'])
    );
  }
}