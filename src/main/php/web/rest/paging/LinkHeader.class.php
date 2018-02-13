<?php namespace web\rest\paging;

use util\URI;

/**
 * Wraps a "Link" header
 *
 * @see   http://tools.ietf.org/html/rfc5988#page-6
 */
class LinkHeader {
  private $links= [];

  /**
   * Creates a new "Link" header
   *
   * @param  [:var] $links A map of rel -> url
   */
  public function __construct($links) {
    foreach ($links as $rel => $link) {
      if ($link instanceof URI) {
        $this->links[$rel]= $link;
      } else if ($link) {
        $this->links[$rel]= new URI($link);
      }
    }
  }

  /** @return bool */
  public function present() { return !empty($this->links); }

  /** @return string */
  public function __toString() {
    $return= '';
    foreach ($this->links as $rel => $link) {
      $return.= ', <'.$link->getURL().'>; rel="'.$rel.'"';
    }
    return (string)substr($return, 2);
  }
}