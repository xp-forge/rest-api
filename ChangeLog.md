Rest API change log
====================

## ?.?.? / ????-??-??

## 1.1.0 / 2018-10-19

* Merged PR #11: Handle `web.Error` - @johannes85, @thekid

## 1.0.0 / 2018-10-06

* **Heads up**: Deprecated `web.rest.ClassesIn`, which is superseded by
  the new `ResourcesIn` class. See PR #10 for migration guide.
  (@thekid)

## 0.9.0 / 2018-08-29

* Merged PR #9: Extract built-in marshalling to xp-forge/marshalling
  library; see https://github.com/xp-forge/marshalling
  (@thekid)

## 0.8.0 / 2018-07-17

* Implemented content type negotation via the HTTP `Accept` header
  (@thekid)
* Merged PR #5: Accept instances or delegates in RestApi constructor
  (@thekid)
* Merged PR #6: Add support for "application/x-www-form-urlencoded"
  (@thekid)

## 0.7.1 / 2018-07-17

* Fixed unmarshalling when invoking setter methods with an array or map
  (@thekid)

## 0.7.0 / 2018-05-25

* Merged PR #3: Two Bugfixes in Marshalling - @johannes85
* Merged PR #4: Intercept invocations, which allow passing functions or
  `web.rest.Interceptor` instances to `RestApi`s to intercept calls to
  the delegates. Usecases are logging, performance profiling, caching,
  input validation and exception handling, for example.
  (@thekid)

## 0.6.0 / 2018-05-23

* Added support for patterns in path segments, e.g. `/users/{id:[0-9]+}`
  (@thekid)
* Fixed `request` injection when parameter was type-hinted - @thekid
* Fixed marshalling not recursing into value objects - @thekid

## 0.5.0 / 2018-04-10

* Changed dependency on `xp-forge/web` to version 1.0.0 since it has
  been released
  (@thekid)

## 0.4.0 / 2018-02-20

* Added optional `base` argument to RestApi constructor. This way, an
  API instance can be mounted at a given base, e.g. `/api/1.0` without
  having to rewrite all the handlers.
  (@thekid)

## 0.3.1 / 2018-02-20

* Fixed `Link: ... rel="next"` header not being shown for items produced
  by a generator (e.g., `yield`).
  (@thekid)

## 0.3.0 / 2018-02-13

* Added `web.rest.Response::export()` for easier unittest - @thekid
* Merged PR #2: Pagination - @thekid
* Added `@$req: request` to pass complete request object - @thekid

## 0.2.0 / 2018-02-13

* Restored HHVM support - @thekid
* Added support for all traversable data structures like `ArrayIterator`
  or `IteratorAggregate` implementations, not just generator functions.
  (@thekid)

## 0.1.1 / 2018-02-13

* Fixed object fields not being converted to their declared types during
  unmarshalling (e.g., using `/** @var T */`).
  (@thekid)

## 0.1.0 / 2018-02-12

* Hello World! First release - @thekid