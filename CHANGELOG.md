CHANGELOG for 1.x
=================

* 1.3.0 (2023-11-16)

  This is a compatibility release to ensure a smooth upgrade path for those not
  yet on Promise v3. We encourage upgrading to the latest version when possible,
  as Promise v3 will be the way forward for this project.

  * Feature: Full PHP 8.3 compatibility.
    (#257 by @clue)

  * Improve test suite, use GitHub actions for continuous integration (CI) and
    report failed assertions.
    (#242 by @clue and #175, #184, #187, #216 and #218 by @SimonFrings)

* 1.2.1 (2016-03-07)

  * Fix `DeferredPromise` to also implement the `CancellablePromiseInterface`.

* 1.2.0 (2016-02-27)

  This release makes the API more compatible with 2.0 while preserving full
  backward compatibility.

  * Introduce new CancellablePromiseInterface implemented by all promises.
  * Add new .cancel() method (part of the CancellablePromiseInterface).


* 1.1.0 (2015-07-01)

  This release makes the API more compatible with 2.0 while preserving full
  backward compatibility.

  * Add `React\Promise\Promise` class.
  * Move methods of `React\Promise\When` and `React\Promise\Util` to functions
    while keeping the classes as a proxy for BC.

* 1.0.4 (2013-04-03)

  * Trigger PHP errors when invalid callback is passed.
  * Fully resolve rejection value before calling rejection handler.
  * Add When::lazy() to create lazy promises which will be initialized once a
    consumer calls the then() method.

* 1.0.3 (2012-11-17)

  * Add `PromisorInterface` for objects that have a `promise()` method.

* 1.0.2 (2012-11-14)

  * Fix bug in When::any() not correctly unwrapping to a single result value
  * $promiseOrValue argument of When::resolve() and When::reject() is now optional

* 1.0.1 (2012-11-13)

  * Prevent deep recursion which was reaching `xdebug.max_nesting_level` default of 100

* 1.0.0 (2012-11-07)

  * First tagged release
