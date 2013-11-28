CHANGELOG
=========

* 2.0.0 (xxxx-xx-xx)

  New major release. The goal was to streamline the API and to make it more
  compliant with other promise libraries and especially with the new upcoming
  [ES6 promises specification](https://github.com/domenic/promises-unwrapping/).

  * Add standalone Promise class.
  * Add new React\Promise\race() function.
  * BC break: Bump minimum PHP version to PHP 5.4.
  * BC break: Remove ResolverInterface and PromiseInterface from Deferred.
  * BC break: Change signature of PromiseInterface.
  * BC break: Remove When and Util classes and move static methods to functions.
  * BC break: FulfilledPromise and RejectedPromise now throw an exception when
    initialized with a promise instead of a value/reason.
  * BC break: React\Promise\Deferred::resolve() and React\Promise\Deferred::reject()
    no longer return a promise.

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
