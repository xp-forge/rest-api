Rest APIs
========================================================================

[![Build status on GitHub](https://github.com/xp-forge/rest-api/workflows/Tests/badge.svg)](https://github.com/xp-forge/rest-api/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_4plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/rest-api/version.svg)](https://packagist.org/packages/xp-forge/rest-api)

Annotation-based REST APIs

Example
-------

```php
use web\rest\{Get, Post, Resource, Response};

#[Resource('/users')]
class Users {

  #[Get('/')]
  public function listUsers($max= 10) {
    // $max comes from request parameter "max", defaulting to 10
    // ...
  }

  #[Get('/{id}')]
  public function findUser($id) {
    // $id is extracted from URL segment
    // ...
  }

  #[Post('/')]
  public function createUser($user) {
    // $user is deserialized from the request body according to its content type
    // ...
    return Response::created('/users/{id}', $id)->entity($created);
  }
}
```

Wire it together in a web application:

```php
use web\Application;

class Service extends Application {

  /** @return [:var] */
  public function routes() {
    return ['/users' => new RestApi(new Users())];
  }
}
```

Run it using:

```bash
$ xp -supervise web Service
@xp.web.Serve(HTTP @ peer.ServerSocket(resource(type= Socket, id= 88) -> tcp://127.0.0.1:8080))
# ...
```

Then call `curl -i localhost:8080/users/1549`.

Parameter sources
-----------------

Method parameters are automatically extracted from URI segments if their name matches the path segment in the curly braces. For requests without bodies (GET, HEAD, DELETE, OPTIONS), the value is extracted from request parameters. For requests with bodies (POST, PUT and PATCH), the body is deserialized and passed.

To supply the source explicitely, you can use parameter attributes:

* `#[Param]` will fetch the parameter from the request parameter named "max".
* `#[Param('maximum')]` will fetch the parameter from the request parameter named "maximum".
* `#[Value]` will use a request value (which was previously passed e.g. inside a filter via `pass()`) for the parameter
* `#[Header('Content-Type')]` will use the *Content-Type* header as value for the parameter
* `#[Entity]` will deserialize the request body and pass its value to the parameter
* `#[Body]` will pass the request body as a string
* `#[Stream]` will pass an `io.streams.InputStream` instance to stream the request body to the parameter
* `#[Request]` will pass the complete `web.Request` object

Parameter conversions
---------------------

Parameters can be converted from their input. This library comes with a built-in conversion named *SeparatedBy*:

```php
use web\rest\{Resource, Get, Param, SeparatedBy};

#[Resource('/api/trainings')]
class Trainings {

  #[Get('/completed')]
  public function completed(#[Param, SeparatedBy(',')] array $orgunits) {
    return $this->repository->find(['status' => 'COMPLETED', 'orgunits' => $orgunits]);
  }
}
```

The *orgunits* parameter can now be supplied in the URL as follows: `https://example.com/api/trainings/completed?orgunits=A,B` and the resulting value inside *$orgunits* will be `["A", "B"]`. User-defined conversions can be supplied by implementing the `web.rest.Conversion` interface.

Matrix parameters
-----------------

This library supports parameters inside path segments, e.g. `https://example.com/api/trainings/status=COMPLETED;orgunits=A,B/authors`:

```php
use web\rest\{Resource, Get, Matrix};

#[Resource('/api/trainings')]
class Trainings {

  #[Get('/{filter}/authors')]
  public function authors(#[Matrix] array $filter) {
    $authors= [];
    foreach ($this->repository->find($filter) as $training) {
      $authors[$training->author->id()]= $training->author;
    }
    return $authors;
  }
}
```

The resulting value inside *$filter* will be `["status" => "COMPLETED", "orgunits" => ["A", "B"]]`.

Return types
------------

Methods can return anything, which is then serialized and written to the response with a "200 OK" status. If you want greater control over the response, you can use the `web.rest.Response` class. It provides a fluent DSL for handling various scenarios.

Example:

```php
return Response::created('/users/{id}', $id)->type('application/vnd.example.type-v2+json')->entity($user);
```

Creation:

* `Response::ok()` - 200 OK
* `Response::created([string $location])` - 201 Created, optionally with a *Location* header
* `Response::accepted()` - 202 Accepted
* `Response::noContent()` - 204 No content
* `Response::see(string $location)` - 302 Found and a *Location* header
* `Response::notModified()` - 304 Not modified
* `Response::notFound([string $message])` - 404 Not found and an optional message, which is serialized
* `Response::notAcceptable([string $message])` - 406 Not acceptable and an optional message, which is serialized
* `Response::error(int $code[, string $message])` - An error and an optional message, which is serialized
* `Response::status(int $code)` - Any other status code

Headers:

* `$response->type(string $mime)` will set the *Content-Type* header
* `$response->header(string $name, string $value)` will set a header with a given name and value

Body:

* `$response->entity(var $value)` will sent a value, serializing it
* `$response->stream(io.streams.InputStream $in[, int $size])` will stream a response
* `$response->body(string $bytes)` will write the given raw bytes to the response

Asynchronous invocation
-----------------------

The following code will run the upload function asynchronously, continuing to serve requests while file contents are being transmitted.

```php
use io\Folder;
use web\rest\{Async, Post, Resource, Response};

#[Resource('/api')]
class Uploads {
  public function __construct(private Folder $folder) { }

  #[Post('/files')]
  public function upload(#[Request] $req) {
    return new Async(function() use($req) {
      if ($multipart= $req->multipart()) {

        foreach ($multipart->files() as $file) {
          yield from $file->transmit($this->folder);
        }
      }

      return Response::ok();
    });
  }
}
```

See also
--------

https://github.com/thekid/shorturl - URL Shortener service 