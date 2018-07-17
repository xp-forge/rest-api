<?php namespace web\rest\unittest;

class Activity {
  private $subscribables;

  public function setSubscribables($subscribables) {
    $this->subscribables= $subscribables;
    return $this;
  }
}
