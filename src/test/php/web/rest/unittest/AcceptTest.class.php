<?php namespace web\rest\unittest;

use unittest\{Test, TestCase, Values};
use web\rest\Accept;

class AcceptTest extends TestCase {

  #[Test]
  public function can_create() {
    new Accept('*/*');
  }

  #[Test]
  public function all_simply_selects_first() {
    $this->assertEquals('text/html', (new Accept('*/*'))->match('text/html'));
  }

  #[Test]
  public function unmatched() {
    $this->assertNull((new Accept('application/json'))->match('text/html'));
  }

  #[Test]
  public function text_all_selects_first_text_type() {
    $this->assertEquals(
      'text/html',
      (new Accept('text/*'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test]
  public function concrete_type_matched() {
    $this->assertEquals(
      'text/plain',
      (new Accept('text/plain'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test, Values(['text/*; q=0.8, text/plain; q=1.0', 'text/plain; q=1.0, text/*; q=0.8', 'text/plain; q=1.0, text/*', 'text/plain, text/*'])]
  public function selects_type_with_higher_q($header) {
    $this->assertEquals(
      'text/plain',
      (new Accept($header))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test]
  public function wikipedia_example_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Accept('text/html; q=1.0, text/*; q=0.8, image/gif; q=0.6, image/jpeg; q=0.6, image/*; q=0.5, */*; q=0.1
'))->match(['text/plain', 'text/html'])
    );
  }
}