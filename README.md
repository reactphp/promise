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
   * [Resolver](#resolver)
3. [API](#api)
   * [Deferred](#deferred-1)
   * [Promise](#promise-1)
   * [Resolver](#resolver-1)
   * [Functions](#functions)
     * [resolve()](#resolve)
     * [reject()](#reject)
     * [all()](#all)
     * [race()](#race)
     * [any()](#any)
     * [some()](#some)
     * [map()](#map)
     * [reduce()](#reduce)
   * [When](#when)
     * [When::all()](#whenall)
     * [When::any()](#whenany)
     * [When::some()](#whensome)
     * [When::map()](#whenmap)
     * [When::reduce()](#whenreduce)
     * [When::resolve()](#whenresolve)
     * [When::reject()](#whenreject)
     * [When::lazy()](#whenlazy)
   * [Promisor](#promisor)
   * [CancellablePromiseInterface](#cancellablepromiseinterface)
     * [CancellablePromiseInterface::cancel()](#cancellablepromiseinterfacecancel)
4. [Examples](#examples)
   * [How to use Deferred](#how-to-use-deferred)
   * [How Promise forwarding works](#how-promise-forwarding-works)
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

It also provides several other useful Promise-related concepts, such as joining
multiple Promises and mapping and reducing collections of Promises.

If you've never heard about Promises before,
[read this first](https://gist.github.com/3889970).

Concepts
--------

### Deferred

A **Deferred** represents a computation or unit of work that may not have
completed yet. Typically (but not always), that computation will be something
that executes asynchronously and completes at some point in the future.

### Promise

While a Deferred represents the computation itself, a **Promise** represents
the result of that computation. Thus, each Deferred has a Promise that acts as
a placeholder for its actual result.

### Resolver

A **Resolver** can resolve, reject or trigger progress notifications on behalf
of a Deferred without knowing any details about consumers.

Sometimes it can be useful to hand out a resolver and allow another
(possibly untrusted) party to provide the resolution value for a Promise.

API
---

### Deferred

A deferred represents an operation whose resolution is pending. It has separate
Promise and Resolver parts that can be safely given out to separate groups of
consumers and producers to allow safe, one-way communication.

``` php
$deferred = new React\Promise\Deferred();

$promise  = $deferred->promise();
$resolver = $deferred->resolver();
```

Although a Deferred has the full Promise + Resolver API, this should be used for
convenience only by the creator of the deferred. Only the Promise and Resolver
should be given to consumers and producers.

``` php
$deferred = new React\Promise\Deferred();

$deferred->then(callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
$deferred->resolve(mixed $promiseOrValue = null);
$deferred->reject(mixed $reason = null);
$deferred->progress(mixed $update = null);
```

The constructor of the `Deferred` accepts an optional `$canceller` argument.
See [Promise](#promise-1) for more information.


``` php
$deferred = new React\Promise\Deferred(function ($resolve, $reject, $progress) {
    throw new \Exception('Promise cancelled');
});

$deferred->cancel();
```

### Promise

The Promise represents the eventual outcome, which is either fulfillment
(success) and an associated value, or rejection (failure) and an associated
reason. The Promise provides mechanisms for arranging to call a function on its
value or reason, and produces a new Promise for the result.

Creates a promise whose state is controlled by the functions passed to
`$resolver`.

```php
$resolver = function (callable $resolve, callable $reject, callable $notify) {
    // Do some work, possibly asynchronously, and then
    // resolve or reject. You can notify of progress events
    // along the way if you want/need.

    $resolve($awesomeResult);
    // or $resolve($anotherPromise);
    // or $reject($nastyError);
    // or $notify($progressNotification);
};

$canceller = function (callable $resolve, callable $reject, callable $progress) {
    // Cancel/abort any running operations like network connections, streams etc.

    $reject(new \Exception('Promise cancelled'));
};

$promise = new React\Promise\Promise($resolver, $canceller);
```

The promise constructor receives a resolver function which will be called
immediately with 3 arguments:

  * `$resolve($value)` - Primary function that seals the fate of the
    returned promise. Accepts either a non-promise value, or another promise.
    When called with a non-promise value, fulfills promise with that value.
    When called with another promise, e.g. `$resolve($otherPromise)`, promise's
    fate will be equivalent to that of `$otherPromise`.
  * `$reject($reason)` - Function that rejects the promise.
  * `$notify($update)` - Function that issues progress events for the promise.

If the resolver throws an exception, the promise will be rejected with that
thrown exception as the rejection reason.

The resolver function will be called immediately, the canceller function only
once all consumers called the `cancel()` method of the promise.

A Promise has a single method `then()` which registers new fulfilled, error and
progress handlers with this Promise (all parameters are optional):

``` php
$newPromise = $promise->then(callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

  * `$fulfilledHandler` will be invoked once the Promise is fulfilled and passed
    the result as the first argument.
  * `$errorHandler` will be invoked once the Promise is rejected and passed the
    reason as the first argument.
  * `$progressHandler` will be invoked whenever the producer of the Promise
    triggers progress notifications and passed a single argument (whatever it
    wants) to indicate progress.

Returns a new Promise that will fulfill with the return value of either
`$fulfilledHandler` or `$errorHandler`, whichever is called, or will reject with
the thrown exception if either throws.

Once in the fulfilled or rejected state, a Promise becomes immutable.
Neither its state nor its result (or error) can be modified.

A Promise makes the following guarantees about handlers registered in
the same call to `then()`:

  1. Only one of `$fulfilledHandler` or `$errorHandler` will be called,
     never both.
  2. `$fulfilledHandler` and `$errorHandler` will never be called more
     than once.
  3. `$progressHandler` may be called multiple times.

#### See also

* [When::resolve()](#whenresolve) - Creating a resolved Promise
* [When::reject()](#whenreject) - Creating a rejected Promise

### Resolver

The Resolver represents the responsibility of fulfilling, rejecting and
notifying the associated Promise.

A Resolver has 3 methods: `resolve()`, `reject()` and `progress()`:

``` php
$resolver->resolve(mixed $result = null);
```

Resolves a Deferred. All consumers are notified by having their
`$fulfilledHandler` (which they registered via `$promise->then()`) called with
`$result`.

If `$result` itself is a promise, the Deferred will transition to the state of
this promise once it is resolved.

``` php
$resolver->reject(mixed $reason = null);
```

Rejects a Deferred, signalling that the Deferred's computation failed.
All consumers are notified by having their `$errorHandler` (which they
registered via `$promise->then()`) called with `$reason`.

If `$reason` itself is a promise, the Deferred will be rejected with the outcome
of this promise regardless whether it fulfills or rejects.

``` php
$resolver->progress(mixed $update = null);
```

Triggers progress notifications, to indicate to consumers that the computation
is making progress toward its result.

All consumers are notified by having their `$progressHandler` (which they
registered via `$promise->then()`) called with `$update`.

### Functions

Useful functions for creating, joining, mapping and reducing collections of
promises.

#### resolve()

```php
$promise = React\Promise\resolve(mixed $promiseOrValue);
```

Creates a promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the resolution value of the
returned promise.

If `$promiseOrValue` is a promise, it will simply be returned.

Note: The promise returned is always a promise implementing
[ExtendedPromiseInterface](#extendedpromiseinterface). If you pass in a custom
promise which only implements [PromiseInterface](#promiseinterface), this
promise will be assimilated to a extended promise following `$promiseOrValue`.

#### reject()

```php
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

```php
$promise = React\Promise\all(array|React\Promise\PromiseInterface $promisesOrValues);
```

Returns a promise that will resolve only once all the items in
`$promisesOrValues` have resolved. The resolution value of the returned promise
will be an array containing the resolution values of each of the items in
`$promisesOrValues`.

#### race()

```php
$promise = React\Promise\race(array|React\Promise\PromiseInterface $promisesOrValues);
```

Initiates a competitive race that allows one winner. Returns a promise which is
resolved in the same way the first settled promise resolves.

#### any()

```php
$promise = React\Promise\any(array|React\Promise\PromiseInterface $promisesOrValues);
```

Returns a promise that will resolve when any one of the items in
`$promisesOrValues` resolves. The resolution value of the returned promise
will be the resolution value of the triggering item.

The returned promise will only reject if *all* items in `$promisesOrValues` are
rejected. The rejection value will be an array of all rejection reasons.

#### some()

```php
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

```php
$promise = React\Promise\map(array|React\Promise\PromiseInterface $promisesOrValues, callable $mapFunc);
```

Traditional map function, similar to `array_map()`, but allows input to contain
promises and/or values, and `$mapFunc` may return either a value or a promise.

The map function receives each item as argument, where item is a fully resolved
value of a promise or value in `$promisesOrValues`.

#### reduce()

```php
$promise = React\Promise\reduce(array|React\Promise\PromiseInterface $promisesOrValues, callable $reduceFunc , $initialValue = null);
```

Traditional reduce function, similar to `array_reduce()`, but input may contain
promises and/or values, and `$reduceFunc` may return either a value or a
promise, *and* `$initialValue` may be a promise or a value for the starting
value.

### When

The `React\Promise\When` class provides useful methods for creating, joining,
mapping and reducing collections of Promises.

> **Note:** Since version 1.1 `React\Promise\When` acts only as a proxy for the [Promise functions](#functions) and its usage is discouraged.

#### When::all()

``` php
$promise = React\Promise\When::all(array|React\Promise\PromiseInterface $promisesOrValues, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

> **Note:** Since version 1.1, [`React\Promise\all()`](#all) is preferred over `React\Promise\When::all()`.
    
Returns a Promise that will resolve only once all the items in
`$promisesOrValues` have resolved. The resolution value of the returned Promise
will be an array containing the resolution values of each of the items in
`$promisesOrValues`.

#### When::any()

``` php
$promise = React\Promise\When::any(array|React\Promise\PromiseInterface $promisesOrValues, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

> **Note:** Since version 1.1, [`React\Promise\any()`](#any) is preferred over `React\Promise\When::any()`.
    
Returns a Promise that will resolve when any one of the items in
`$promisesOrValues` resolves. The resolution value of the returned Promise
will be the resolution value of the triggering item.

The returned Promise will only reject if *all* items in `$promisesOrValues` are
rejected. The rejection value will be an array of all rejection reasons.

#### When::some()

``` php
$promise = React\Promise\When::some(array|React\Promise\PromiseInterface $promisesOrValues, integer $howMany, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

> **Note:** Since version 1.1, [`React\Promise\some()`](#some) is preferred over `React\Promise\When::some()`.
    
Returns a Promise that will resolve when `$howMany` of the supplied items in
`$promisesOrValues` resolve. The resolution value of the returned Promise
will be an array of length `$howMany` containing the resolution values of the
triggering items.

The returned Promise will reject if it becomes impossible for `$howMany` items
to resolve (that is, when `(count($promisesOrValues) - $howMany) + 1` items
reject). The rejection value will be an array of
`(count($promisesOrValues) - $howMany) + 1` rejection reasons.

#### When::map()

``` php
$promise = React\Promise\When::map(array|React\Promise\PromiseInterface $promisesOrValues, callable $mapFunc);
```

> **Note:** Since version 1.1, [`React\Promise\map()`](#map) is preferred over `React\Promise\When::map()`.
    
Traditional map function, similar to `array_map()`, but allows input to contain
Promises and/or values, and `$mapFunc` may return either a value or a Promise.

The map function receives each item as argument, where item is a fully resolved
value of a Promise or value in `$promisesOrValues`.

#### When::reduce()

``` php
$promise = React\Promise\When::reduce(array|React\Promise\PromiseInterface $promisesOrValues, callable $reduceFunc , $initialValue = null);
```

> **Note:** Since version 1.1, [`React\Promise\reduce()`](#reduce) is preferred over `React\Promise\When::reduce()`.
    
Traditional reduce function, similar to `array_reduce()`, but input may contain
Promises and/or values, and `$reduceFunc` may return either a value or a
Promise, *and* `$initialValue` may be a Promise or a value for the starting
value.

#### When::resolve()

``` php
$promise = React\Promise\When::resolve(mixed $promiseOrValue);
```

> **Note:** Since version 1.1, [`React\Promise\resolve()`](#resolve) is preferred over `React\Promise\When::resolve()`.
    
Creates a resolved Promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the resolution value of the
returned Promise.

If `$promiseOrValue` is a Promise, it will simply be returned.

#### When::reject()

``` php
$promise = React\Promise\When::reject(mixed $promiseOrValue);
```

> **Note:** Since version 1.1, [`React\Promise\reject()`](#reject) is preferred over `React\Promise\When::reject()`.
    
Creates a rejected Promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the rejection value of the
returned Promise.

If `$promiseOrValue` is a Promise, its completion value will be the rejected
value of the returned Promise.

This can be useful in situations where you need to reject a Promise without
throwing an exception. For example, it allows you to propagate a rejection with
the value of another Promise.

#### When::lazy()

``` php
$promise = React\Promise\When::lazy(callable $factory);
```

Creates a Promise which will be lazily initialized by `$factory` once a consumer
calls the `then()` method.

```php
$factory = function () {
    $deferred = new React\Promise\Deferred();

    // Do some heavy stuff here and resolve the Deferred once completed

    return $deferred->promise();
};

$promise = React\Promise\When::lazy($factory);

// $factory will only be executed once we call then()
$promise->then(function ($value) {
});
```

### Promisor

The `React\Promise\PromisorInterface` provides a common interface for objects
that provide a promise. `React\Promise\Deferred` implements it, but since it
is part of the public API anyone can implement it.

### CancellablePromiseInterface

A cancellable promise provides a mechanism for consumers to notify the creator
of the promise that they are not longer interested in the result of an
operation.

#### CancellablePromiseInterface::cancel()

``` php
$promise->cancel();
```

The `cancel()` method notifies the creator of the promise that there is no
further interest in the results of the operation.

Once a promise is settled (either resolved or rejected), calling `cancel()` on
a promise has no effect.

#### Implementations

* [Deferred](#deferred-1)
* [Promise](#promise-1)
* [FulfilledPromise](#fulfilledpromise)
* [RejectedPromise](#rejectedpromise)
* [LazyPromise](#lazypromise)

Examples
--------

### How to use Deferred

``` php
function getAwesomeResultPromise()
{
    $deferred = new React\Promise\Deferred();

    // Pass only the Resolver, to provide the resolution value for the Promise
    computeAwesomeResultAsynchronously($deferred->resolver());

    // Return only the Promise, so that the caller cannot
    // resolve, reject, or otherwise muck with the original Deferred.
    return $deferred->promise();
}

getAwesomeResultPromise()
    ->then(
        function ($result) {
            // Deferred resolved, do something with $result
        },
        function ($reason) {
            // Deferred rejected, do something with $reason
        },
        function ($update) {
            // Progress notification triggered, do something with $update
        }
    );
```

### How Promise forwarding works

A few simple examples to show how the mechanics of Promises/A forwarding works.
These examples are contrived, of course, and in real usage, Promise chains will
typically be spread across several function calls, or even several levels of
your application architecture.

#### Resolution forwarding

Resolved Promises forward resolution values to the next Promise.
The first Promise, `$deferred->promise()`, will resolve with the value passed
to `$deferred->resolve()` below.

Each call to `then()` returns a new Promise that will resolve with the return
value of the previous handler. This creates a Promise "pipeline".

``` php
$deferred = new React\Promise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        // $x will be the value passed to $deferred->resolve() below
        // and returns a *new Promise* for $x + 1
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

Rejected Promises behave similarly, and also work similarly to try/catch:
When you catch an exception, you must rethrow for it to propagate.

Similarly, when you handle a rejected Promise, to propagate the rejection,
"rethrow" it by either returning a rejected Promise, or actually throwing
(since Promise translates thrown exceptions into rejections)

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
        return React\Promise\When::reject((integer) $x->getMessage() + 1);
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
