Web applications for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/rest-api.png)](http://travis-ci.org/xp-forge/rest-api)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/rest-api/version.png)](https://packagist.org/packages/xp-forge/rest-api)

Example
-------

```php
class Users {

  #[@get('/users')]
  public function listUsers() {
    // ...
  }

  #[@get('/users/{id}')]
  public function getUser($id) {
    // ...
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