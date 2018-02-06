<?php namespace web\rest;

use lang\Throwable;

class Response {
  private $status;
  private $headers= [];
  private $body= null;

  /** @param int $status */
  private function __construct($status) {
    $this->status= $status;
  }

  /**
   * Creates a new response instance with the status code set to 200 (OK)
   *
   * @return self
   */
  public static function ok() {
    return new self(200);
  }

  /**
   * Creates a new response instance with the status code set to 201 (Created)
   * and an optional location.
   *
   * @param  string $location
   * @return self
   */
  public static function created($location= null) {
    $self= new self(201);
    if (null !== $location) $self->headers['Location']= $location;
    return $self;
  }

  /**
   * Creates a new response instance with the status code set to 204 (No content)
   *
   * @return self
   */
  public static function noContent() {
    return new self(204);
  }

  /**
   * Creates a new response instance with the status code set to 302 (See other)
   * and a specified location.
   *
   * @param  string $location
   * @return self
   */
  public static function see($location) {
    $self= new self(302);
    $self->headers['Location']= $location;
    return $self;
  }

  /**
   * Creates a new response instance with the status code set to 304 (Not modified)
   *
   * @return self
   */
  public static function notModified() {
    return new self(304);
  }

  /**
   * Creates a new response instance with the status code set to 404 (Not found)
   *
   * @param  string|lang.Throwable $cause Optional
   * @return self
   */
  public static function notFound($cause= null) {
    return self::error(404, $cause);
  }

  /**
   * Creates a new response instance with the status code set to 406 (Not acceptable)
   *
   * @param  string|lang.Throwable $cause Optional
   * @return self
   */
  public static function notAcceptable($cause= null) {
    return self::error(406, $cause);
  }

  /**
   * Creates a new response instance with the status code optionally set to a given
   * error code (defaulting to 500 - Internal Server Error).
   *
   * @param  int $code
   * @param  string|lang.Throwable $cause Optional
   * @return self
   */
  public static function error($code= 500, $cause= null) {
    $self= new self($code);
    if (null !== $cause) {
      $error= ['status' => $code, 'message' => $cause instanceof Throwable ? $cause->getMessage() : $cause];
      $self->body= function($res, $format) use($error) {
        $format->write($res, $error);
      };
    }
    return $self;
  }

  /**
   * Creates a new response instance with the status code set to a given status.
   *
   * @param  int $code
   * @return self
   */
  private static function status($code) {
    return new self($code);
  }

  /**
   * Sets content-type
   *
   * @param  string $mime
   * @return self
   */
  public function type($mime) {
    $this->headers['Content-Type']= $mime;
    return $this;
  }

  /**
   * Sets a header
   *
   * @param  string $name
   * @param  string $value
   * @return self
   */
  public function header($name, $value) {
    $this->headers[$name]= $value;
    return $this;
  }

  /**
   * Sends a entity
   *
   * @param  var $value
   * @return self
   */
  public function entity($value) {
    $this->body= function($res, $format) use($value) {
      $format->write($res, $value);
    };
    return $this;
  }

  /**
   * Sends a stream
   *
   * @param  io.streams.InputStream $in
   * @param  int $size Optional size (in bytes), omit to use chunked transfer
   * @return self
   */
  public function stream($in, $size= null) {
    $this->body= function($res, $format) use($in, $size) {
      $out= $res->stream($size);
      try {
        while ($in->available()) {
          $out->write($in->read());
        }
      } finally {
        $in->close();
        $out->close();
      }
    };
    return $this;
  }

  /**
   * Sends given bytes as response body
   *
   * @param  string $bytes
   * @return self
   */
  public function body($bytes) {
    $this->body= function($res, $format) use($bytes) {
      $out= $res->stream(strlen($bytes));
      $out->write($bytes);
      $out->close();
    };
    return $this;
  }

  /**
   * Transmits this response value
   *
   * @param  web.Response $response
   * @param  web.rest.format.EntityFormat $format
   * @return void
   */
  public function transmit($response, $format) {
    $response->answer($this->status);
    $response->header('Content-Type', $format->mimeType());

    // Copy headers, overwriting default content type if necessary
    foreach ($this->headers as $name => $value) {
      $response->header($name, $value);
    }

    if ($f= $this->body) {
      $f($response, $format);
    }
  }
}