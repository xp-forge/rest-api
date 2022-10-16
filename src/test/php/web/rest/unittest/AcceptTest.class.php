<?php namespace web\rest\unittest;

use unittest\{Assert, Test, Values};
use web\rest\Accept;

class AcceptTest {

  #[Test]
  public function can_create() {
    new Accept('*/*');
  }

  #[Test]
  public function all_simply_selects_first() {
    Assert::equals('text/html', (new Accept('*/*'))->match('text/html'));
  }

  #[Test]
  public function unmatched() {
    Assert::null((new Accept('application/json'))->match('text/html'));
  }

  #[Test]
  public function text_all_selects_first_text_type() {
    Assert::equals(
      'text/html',
      (new Accept('text/*'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test]
  public function concrete_type_matched() {
    Assert::equals(
      'text/plain',
      (new Accept('text/plain'))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test]
  public function may_include_charset() {
    Assert::equals(
      'application/json',
      (new Accept('application/json; charset=utf-8'))->match('application/json')
    );
  }

  #[Test, Values(['text/*; q=0.8, text/plain; q=1.0', 'text/plain; q=1.0, text/*; q=0.8', 'text/plain; q=1.0, text/*', 'text/plain, text/*'])]
  public function selects_type_with_higher_q($header) {
    Assert::equals(
      'text/plain',
      (new Accept($header))->match(['application/json', 'text/html', 'text/plain'])
    );
  }

  #[Test]
  public function wikipedia_example_match_html_vs_plaintext() {
    Assert::equals(
      'text/html', 
      (new Accept('text/html; q=1.0, text/*; q=0.8, image/gif; q=0.6, image/jpeg; q=0.6, image/*; q=0.5, */*; q=0.1
'))->match(['text/plain', 'text/html'])
    );
  }
}