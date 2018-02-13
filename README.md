Rest APIs
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/rest-api.png)](http://travis-ci.org/xp-forge/rest-api)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/rest-api/version.png)](https://packagist.org/packages/xp-forge/rest-api)

Annotation-based REST APIs

Example
-------

```php
use web\rest\Response;

class Users {

  #[@get('/users'), @$max: param]
  public function listUsers($max= 10) {
    // ...
  }

  #[@get('/users/{id}')]
  public function getUser($id) {
    // ...
  }

  #[@post('/users'), @$user: entity]
  public function createUser($user) {
    // ...
    return Response::created('/users/'.$id)->entity($created);
  }
}
```

Wire it together in a web application:

```php
class Service extends \web\Application {

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

Method parameters are automatically extracted from URI segments if their name matches the string in the curly braces. For other sources, you will need to supply a method annotation in the form `$<param>: <source>[(<name>)]`:

* `@$max: param` will fetch the $max parameter from the request parameter named "max".
* `@$max: param('maximum')` will fetch the $max parameter from the request parameter named "maximum".
* `@$user: value` will use a request value (which was previously passed e.g. inside a filter via `pass()`) for $user
* `@$type: header('Content-Type')` will use the *Content-Type* header as value for $type
* `@$attributes: entity` will deserialize the request body and pass its value to $attributes
* `@$upload: stream` will pass an `io.streams.InputStream` instance to stream the request body to $upload
* `@$bytes: body` will pass the request body as a string
* `@$req: request` will pass the complete request object


Return types
------------

Methods can return anything, which is then serialized and written to the response with a "200 OK" status. If you want greater control over the response, you can use the `web.rest.Response` class. It provides a fluent DSL for handling various scenarios.

Example:

```php
return Response::created('/users/'.$id)->type('application/vnd.example.customer-v2+json')->entity($user);
```

Creation:

* `Response::ok()` - 200 OK
* `Response::created(string? $location)` - 201 Created, optionally with a *Location* header
* `Response::noContent()` - 204 No content
* `Response::see(string $location)` - 302 Found and a *Location* header
* `Response::notModified()` - 304 Not modified
* `Response::notFound(string? $message)` - 404 Not found and an optional message, which is serialized
* `Response::notAcceptable(string? $message)` - 406 Not acceptable and an optional message, which is serialized
* `Response::error(int $code, string? $message)` - An error and an optional message, which is serialized
* `Response::status(int $code)` - Any other status code

Headers:

* `$response->type(string $mime)` will set the *Content-Type* header
* `$response->header(string $name, string $value)` will set a header with a given name and value

Body:

* `$response->entity(var $value)` will sent a value, serializing it
* `$response->stream(io.streams.InputStream $in, int? $size)` will stream a response
* `$response->body(string $bytes)` will write the given raw bytes to the response
