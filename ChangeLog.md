Rest API change log
====================

## ?.?.? / ????-??-??

## 5.0.0 / 2025-05-04

**Heads up:** Dropped support for PHP < 7.4, see xp-framework/rfc#343
  (@thekid)
* Added PHP 8.5 to test matrix - @thekid

## 4.4.0 / 2024-03-24

* Made compatible with XP 12 - @thekid

## 4.3.0 / 2024-01-30

* Added PHP 8.4 to the test matrix - @thekid
* Made this library compatible with xp-forge/web version 4.0 - @thekid

## 4.2.0 / 2023-09-30

* Merged PR #26: Add possibility to convert input parameters via
  annotations, see #25. Includes support for matrix parameters.
  (@thekid)

## 4.1.0 / 2023-09-24

* Added compatibility with `xp-forge/marshalling` v2.0.0 - @thekid

## 4.0.1 / 2023-07-31

* Fixed annotation argument not being taken into account - @thekid

## 4.0.0 / 2023-07-25

* **Heads up:** Removed deprecated `web.rest.ClassesIn` - @thekid
* Merged PR #24: Migrate to new reflection library - @thekid
* Merged PR #23: Migrate to new testing library - @thekid

## 3.5.0 / 2022-11-19

* Merged PR #22: Change `Response::stream()` to use async APIs - @thekid

## 3.4.1 / 2022-11-03

* Ensured default headers are always set, not just when returning a
  `web.rest.Reponse` instance
  (@thekid)

## 3.4.0 / 2022-11-02

* Added *X-Content-Type-Options: nosniff* and *Cache-Control: no-cache*
  to prevent errors in the browser console when using REST APIs via
  XHR / fetch. See issue #21
  (@thekid)

## 3.3.0 / 2022-10-16

* Merged PR #20: Return "application/json; charset=utf-8" as content
  type, requested to be changed in #19.
  (@thekid)

## 3.2.0 / 2022-09-30

* Merged PR #17: Add support for asynchronously running handler code
  (@thekid)

## 3.1.0 / 2022-09-26

* Merged PR #16: Send a "HTTP/1.1 100 Continue" if we get an *Expect*
  header with "100-continue"
  (@thekid)

## 3.0.2 / 2021-10-21

* Made library compatible with new major release of `xp-forge/json`
  (@thekid)
* Made library compatible with XP 11, newer `xp-framework/zip` library
  (@thekid)

## 3.0.1 / 2021-09-26

* Fixed PHP 8.1 compatibility - @thekid
* Made compatible with XP web 3.0, see xp-forge/web#83 - @thekid

## 3.0.0 / 2020-10-10

* Merged PR #13: Parameter defaults. With this pull request, it's no longer
  necessary to supply parameter annotations for the typical case.
  (@thekid)
* Merged PR #14: Expand curly braces inside location in `Response::see()`
  and `Response::created()`.
  (@thekid)
* Merged PR #12: Inject request into parameters with `web.Request` type
  (@thekid)

## 2.0.0 / 2020-04-10

* Implemented xp-framework/rfc#334: Drop PHP 5.6:
  . **Heads up:** Minimum required PHP version now is PHP 7.0.0
  . Rewrote code base, grouping use statements
  . Rewrote `isset(X) ? X : default` to `X ?? default`
  (@thekid)

## 1.1.3 / 2020-04-10

* Implemented RFC #335: Remove deprecated key/value pair annotation syntax
  (@thekid)

## 1.1.2 / 2019-12-01

* Made compatible with XP 10 - @thekid

## 1.1.1 / 2019-01-22

* Made compatible with `xp-forge/marshalling` 0.3.0 - @thekid

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