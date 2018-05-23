Rest API change log
====================

## ?.?.? / ????-??-??

## 0.5.1 / 2018-05-23

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