React/Promise
=============

A lightweight implementation of
[CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

[![Build Status](https://secure.travis-ci.org/reactphp/promise.png?branch=master)](http://travis-ci.org/reactphp/promise)

Table of Contents
-----------------

1. [Introduction](#introduction)
2. [Concepts](#concepts)
   * [Deferred](#deferred)
   * [Promise](#promise)
3. [API](#api)
   * [Deferred](#deferred-1)
     * [Deferred::promise()](#deferredpromise)
     * [Deferred::resolve()](#deferredresolve)
     * [Deferred::reject()](#deferredreject)
     * [Deferred::progress()](#deferredprogress)
   * [PromiseInterface](#promiseinterface)
     * [PromiseInterface::then()](#promiseinterfacethen)
   * [Promise](#promise-1)
   * [FulfilledPromise](#fulfilledpromise)
   * [RejectedPromise](#rejectedpromise)
   * [LazyPromise](#lazypromise)
   * [Functions](#functions)
     * [resolve()](#resolve)
     * [reject()](#reject)
     * [all()](#all)
     * [race()](#race)
     * [any()](#any)
     * [some()](#some)
     * [map()](#map)
     * [reduce()](#reduce)
   * [PromisorInterface](#promisorinterface)
4. [Examples](#examples)
   * [How to use Deferred](#how-to-use-deferred)
   * [How promise forwarding works](#how-promise-forwarding-works)
     * [Resolution forwarding](#resolution-forwarding)
     * [Rejection forwarding](#rejection-forwarding)
     * [Mixed resolution and rejection forwarding](#mixed-resolution-and-rejection-forwarding)
     * [Progress event forwarding](#progress-event-forwarding)
5. [Credits](#credits)
6. [License](#license)

Introduction
------------

React/Promise is a library implementing
[CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

It also provides several other useful promise-related concepts, such as joining
multiple promises and mapping and reducing collections of promises.

If you've never heard about promises before,
[read this first](https://gist.github.com/3889970).

Concepts
--------

### Deferred

A **Deferred** represents a computation or unit of work that may not have
completed yet. Typically (but not always), that computation will be something
that executes asynchronously and completes at some point in the future.

### Promise

While a deferred represents the computation itself, a **Promise** represents
the result of that computation. Thus, each deferred has a promise that acts as
a placeholder for its actual result.

API
---

### Deferred

A deferred represents an operation whose resolution is pending. It has separate
promise and resolver parts.

``` php
$deferred = new React\Promise\Deferred();

$promise = $deferred->promise();

$deferred->resolve(mixed $value = null);
$deferred->reject(mixed $reason = null);
$deferred->progress(mixed $update = null);
```

The `promise` method returns the promise of the deferred.

The `resolve` and `reject` methods control the state of the deferred.

The `progress` method is for progress notification.

#### Deferred::promise()

``` php
$promise = $deferred->promise();
```

Returns the promise of the deferred, which you can hand out to others while
keeping the authority to modify its state to yourself.

#### Deferred::resolve()

``` php
$deferred->resolve(mixed $value = null);
```

Resolves the promise returned by `promise()`. All consumers are notified by
having `$onFulfilled` (which they registered via `$promise->then()`) called with
`$value`.

If `$value` itself is a promise, the promise will transition to the state of
this promise once it is resolved.

#### Deferred::reject()

``` php
$resolver->reject(mixed $reason = null);
```

Rejects the promise returned by `promise()`, signalling that the deferred's
computation failed.
All consumers are notified by having `$onRejected` (which they registered via
`$promise->then()`) called with `$reason`.

If `$reason` itself is a promise, the promise will be rejected with the outcome
of this promise regardless whether it fulfills or rejects.

#### Deferred::progress()

``` php
$resolver->progress(mixed $update = null);
```

Triggers progress notifications, to indicate to consumers that the computation
is making progress toward its result.

All consumers are notified by having `$onProgress` (which they registered via
`$promise->then()`) called with `$update`.

### PromiseInterface

The promise interface provides the common interface for all promise
implementations.

A promise represents an eventual outcome, which is either fulfillment (success)
and an associated value, or rejection (failure) and an associated reason.

Once in the fulfilled or rejected state, a promise becomes immutable.
Neither its state nor its result (or error) can be modified.

#### PromiseInterface::then()

``` php
$newPromise = $promise->then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null);
```

The `then()` method registers new fulfilled, rejection and progress handlers
with this promise (all parameters are optional):

  * `$onFulfilled` will be invoked once the promise is fulfilled and passed
    the result as the first argument.
  * `$onRejected` will be invoked once the promise is rejected and passed the
    reason as the first argument.
  * `$onProgress` will be invoked whenever the producer of the promise
    triggers progress notifications and passed a single argument (whatever it
    wants) to indicate progress.

It returns a new promise that will fulfill with the return value of either
`$onFulfilled` or `$onRejected`, whichever is called, or will reject with
the thrown exception if either throws.

A promise makes the following guarantees about handlers registered in
the same call to `then()`:

  1. Only one of `$onFulfilled` or `$onRejected` will be called,
     never both.
  2. `$onFulfilled` and `$onRejected` will never be called more
     than once.
  3. `$onProgress` may be called multiple times.

#### Implementations

* [Promise](#promise-1)
* [FulfilledPromise](#fulfilledpromise)
* [RejectedPromise](#rejectedpromise)
* [LazyPromise](#lazypromise)

#### See also

* [resolve()](#resolve) - Creating a resolved promise
* [reject()](#reject) - Creating a rejected promise

### Promise

Creates a promise whose state is controlled by the functions passed to
`$resolver`.

``` php
$resolver = function (callable $resolve, callable $reject, callable $progress) {
    // Do some work, possibly asynchronously, and then
    // resolve or reject. You can notify of progress events
    // along the way if you want/need.

    $resolve($awesomeResult);
    // or $resolve($anotherPromise);
    // or $reject($nastyError);
    // or $progress($progressNotification);
};

$promise = new React\Promise\Promise($resolver);
```

The promise constructor receives a resolver function which will be called
with 3 arguments:

  * `$resolve($value)` - Primary function that seals the fate of the
    returned promise. Accepts either a non-promise value, or another promise.
    When called with a non-promise value, fulfills promise with that value.
    When called with another promise, e.g. `$resolve($otherPromise)`, promise's
    fate will be equivalent to that of `$otherPromise`.
  * `$reject($reason)` - Function that rejects the promise.
  * `$progress($update)` - Function that issues progress events for the promise.

If the resolver throws an exception, the promise will be rejected with that
thrown exception as the rejection reason.


### FulfilledPromise

Creates a already fulfilled promise.

```php
$promise = React\Promise\FulfilledPromise($value);
```

Note, that `$value` **cannot** be a promise. It's recommended to use
[resolve()](#resolve) for creating resolved promises.

### RejectedPromise

Creates a already rejected promise.

```php
$promise = React\Promise\RejectedPromise($reason);
```

Note, that `$reason` **cannot** be a promise. It's recommended to use
[reject()](#reject) for creating rejected promises.

### LazyPromise

Creates a promise which will be lazily initialized by `$factory` once a consumer
calls the `then()` method.

```php
$factory = function () {
    $deferred = new React\Promise\Deferred();

    // Do some heavy stuff here and resolve the deferred once completed

    return $deferred->promise();
};

$promise = React\Promise\LazyPromise($factory);

// $factory will only be executed once we call then()
$promise->then(function ($value) {
});
```

### Functions

Useful functions for creating, joining, mapping and reducing collections of
promises.

#### resolve()

``` php
$promise = React\Promise\resolve(mixed $promiseOrValue);
```

Creates a resolved promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the resolution value of the
returned promise.

If `$promiseOrValue` is a promise, it will simply be returned.

#### reject()

``` php
$promise = React\Promise\reject(mixed $promiseOrValue);
```

Creates a rejected promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the rejection value of the
returned promise.

If `$promiseOrValue` is a promise, its completion value will be the rejected
value of the returned promise.

This can be useful in situations where you need to reject a promise without
throwing an exception. For example, it allows you to propagate a rejection with
the value of another promise.

#### all()

``` php
$promise = React\Promise\all(array|React\Promise\PromiseInterface $promisesOrValues);
```

Returns a promise that will resolve only once all the items in
`$promisesOrValues` have resolved. The resolution value of the returned promise
will be an array containing the resolution values of each of the items in
`$promisesOrValues`.

#### race()

``` php
$promise = React\Promise\race(array|React\Promise\PromiseInterface $promisesOrValues);
```

Initiates a competitive race that allows one winner. Returns a promise which is
resolved in the same way the first settled promise resolves.

#### any()

``` php
$promise = React\Promise\any(array|React\Promise\PromiseInterface $promisesOrValues);
```

Returns a promise that will resolve when any one of the items in
`$promisesOrValues` resolves. The resolution value of the returned promise
will be the resolution value of the triggering item.

The returned promise will only reject if *all* items in `$promisesOrValues` are
rejected. The rejection value will be an array of all rejection reasons.

#### some()

``` php
$promise = React\Promise\some(array|React\Promise\PromiseInterface $promisesOrValues, integer $howMany);
```

Returns a promise that will resolve when `$howMany` of the supplied items in
`$promisesOrValues` resolve. The resolution value of the returned promise
will be an array of length `$howMany` containing the resolution values of the
triggering items.

The returned promise will reject if it becomes impossible for `$howMany` items
to resolve (that is, when `(count($promisesOrValues) - $howMany) + 1` items
reject). The rejection value will be an array of
`(count($promisesOrValues) - $howMany) + 1` rejection reasons.

#### map()

``` php
$promise = React\Promise\map(array|React\Promise\PromiseInterface $promisesOrValues, callable $mapFunc);
```

Traditional map function, similar to `array_map()`, but allows input to contain
promises and/or values, and `$mapFunc` may return either a value or a promise.

The map function receives each item as argument, where item is a fully resolved
value of a promise or value in `$promisesOrValues`.

#### reduce()

``` php
$promise = React\Promise\reduce(array|React\Promise\PromiseInterface $promisesOrValues, callable $reduceFunc , $initialValue = null);
```

Traditional reduce function, similar to `array_reduce()`, but input may contain
promises and/or values, and `$reduceFunc` may return either a value or a
promise, *and* `$initialValue` may be a promise or a value for the starting
value.

### PromisorInterface

The `React\Promise\PromisorInterface` provides a common interface for objects
that provide a promise. `React\Promise\Deferred` implements it, but since it
is part of the public API anyone can implement it.

Examples
--------

### How to use Deferred

``` php
function getAwesomeResultPromise()
{
    $deferred = new React\Promise\Deferred();

    // Pass only the Resolver, to provide the resolution value for the promise
    computeAwesomeResultAsynchronously($deferred->resolver());

    // Return only the promise, so that the caller cannot
    // resolve, reject, or otherwise muck with the original deferred.
    return $deferred->promise();
}

getAwesomeResultPromise()
    ->then(
        function ($value) {
            // Deferred resolved, do something with $value
        },
        function ($reason) {
            // Deferred rejected, do something with $reason
        },
        function ($update) {
            // Progress notification triggered, do something with $update
        }
    );
```

### How promise forwarding works

A few simple examples to show how the mechanics of Promises/A forwarding works.
These examples are contrived, of course, and in real usage, promise chains will
typically be spread across several function calls, or even several levels of
your application architecture.

#### Resolution forwarding

Resolved promises forward resolution values to the next promise.
The first promise, `$deferred->promise()`, will resolve with the value passed
to `$deferred->resolve()` below.

Each call to `then()` returns a new promise that will resolve with the return
value of the previous handler. This creates a promise "pipeline".

``` php
$deferred = new React\Promise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        // $x will be the value passed to $deferred->resolve() below
        // and returns a *new promise* for $x + 1
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 2
        // This handler receives the return value of the
        // previous handler.
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 3
        // This handler receives the return value of the
        // previous handler.
        return $x + 1;
    })
    ->then(function ($x) {
        // $x === 4
        // This handler receives the return value of the
        // previous handler.
        echo 'Resolve ' . $x;
    });

$deferred->resolve(1); // Prints "Resolve 4"
```

#### Rejection forwarding

Rejected promises behave similarly, and also work similarly to try/catch:
When you catch an exception, you must rethrow for it to propagate.

Similarly, when you handle a rejected promise, to propagate the rejection,
"rethrow" it by either returning a rejected promise, or actually throwing
(since promise translates thrown exceptions into rejections)

``` php
$deferred = new React\Promise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        throw new \Exception($x + 1);
    })
    ->then(null, function (\Exception $x) {
        // Propagate the rejection
        throw $x;
    })
    ->then(null, function (\Exception $x) {
        // Can also propagate by returning another rejection
        return React\Promise\reject((integer) $x->getMessage() + 1);
    })
    ->then(null, function ($x) {
        echo 'Reject ' . $x; // 3
    });

$deferred->resolve(1);  // Prints "Reject 3"
```

#### Mixed resolution and rejection forwarding

Just like try/catch, you can choose to propagate or not. Mixing resolutions and
rejections will still forward handler results in a predictable way.

``` php
$deferred = new React\Promise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        return $x + 1;
    })
    ->then(function ($x) {
        throw \Exception($x + 1);
    })
    ->then(null, function (\Exception $x) {
        // Handle the rejection, and don't propagate.
        // This is like catch without a rethrow
        return (integer) $x->getMessage() + 1;
    })
    ->then(function ($x) {
        echo 'Mixed ' . $x; // 4
    });

$deferred->resolve(1);  // Prints "Mixed 4"
```

#### Progress event forwarding

In the same way as resolution and rejection handlers, your progress handler
**MUST** return a progress event to be propagated to the next link in the chain.
If you return nothing, `null` will be propagated.

Also in the same way as resolutions and rejections, if you don't register a
progress handler, the update will be propagated through.

If your progress handler throws an exception, the exception will be propagated
to the next link in the chain. The best thing to do is to ensure your progress
handlers do not throw exceptions.

This gives you the opportunity to transform progress events at each step in the
chain so that they are meaningful to the next step. It also allows you to choose
not to transform them, and simply let them propagate untransformed, by not
registering a progress handler.

``` php
$deferred = new React\Promise\Deferred();

$deferred->promise()
    ->then(null, null, function ($update) {
        return $update + 1;
    })
    ->then(null, null, function ($update) {
        echo 'Progress ' . $update; // 2
    });

$deferred->progress(1);  // Prints "Progress 2"
```

Credits
-------

React/Promise is a port of [when.js](https://github.com/cujojs/when)
by [Brian Cavalier](https://github.com/briancavalier).

Also, large parts of the documentation have been ported from the when.js
[Wiki](https://github.com/cujojs/when/wiki) and the
[API docs](https://github.com/cujojs/when/blob/master/docs/api.md).

License
-------

React/Promise is released under the [MIT](https://github.com/reactphp/promise/blob/master/LICENSE) license.
