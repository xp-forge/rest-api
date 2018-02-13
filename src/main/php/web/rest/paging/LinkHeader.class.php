<?php namespace web\rest\paging;

use lang\Value;
use util\URI;

/**
 * Wraps a "Link" header
 *
 * @see   http://tools.ietf.org/html/rfc5988#page-6
 */
class LinkHeader implements Value {
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
      $return.= ', <'.(string)$link.'>; rel="'.$rel.'"';
    }
    return (string)substr($return, 2);
  }

  /** @return string */
  public function hashCode() { return md5($this->__toString()); }

  /** @return string */
  public function toString() { return nameof($this).'('.$this->__toString().')'; }

  /**
   * Compare
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? strcmp($this->__toString(), $value->__toString()) : 1;
  }
}