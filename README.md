Promise
=======

A lightweight implementation of
[CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

Promise is a port of [when.js](https://github.com/cujojs/when)
by [Brian Cavalier](https://github.com/briancavalier).

Also, large parts of the documentation have been ported from the
[when.js Wiki](https://github.com/cujojs/when/wiki).

Introduction
------------

Promise is a library implementing
[CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

It also provides several other useful Promise-related concepts, such as joining
multiple promises and mapping and reducing collections of promises.

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

A **Resolver** can resolve, reject or trigger progress notification on behalf of
a Deferred without knowing any details about consumers.

Sometimes it can be useful to hand out a resolver and allow another
(possibly untrusted) party to provide the resolution value for a promise.

API
---

### Deferred

A Deferred has the full Promise + Resolver API:

``` php
$deferred = new Promise\Deferred();

$deferred->then(callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
$deferred->resolve(mixed $promiseOrValue = null);
$deferred->reject(mixed $error = null);
$deferred->progress(mixed $update = null);
```

It can also hand out separate Promise and Resolver parts that can be safely
given out to calling code:

``` php
$deferred = new Promise\Deferred();

$promise  = $deferred->promise();
$resolver = $deferred->resolver();
```

### Promise

A Promise has a single method `then()` which registers new fulfilled, error and
progress handlers with this Promise (all parameters are optional):

``` php
$promise->then(callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

As per the Promises/A spec, `then()` returns a new Promise that will be resolved
with the return value of `$fulfilledHandler` if Promise is fulfilled, or with
the return value of `$errorHandler` if Promise is rejected.

A Promise starts in an unresolved state. At some point the computation will
either complete successfully, thus producing a result, or fail, either
generating some sort of error why it could not complete.

If the computation completes successfully, the Promise will transition to the
resolved state and the `$fulfilledHandler` will be invoked and passed the
result as the first argument.

If the computation fails, the Promise will transition to the rejected
state and `$errorHandler` will be invoked and passed the error as the first
argument.

The producer of this Promise may trigger progress notifications to
indicate that the computation is making progress toward its result.
For each progress notification, `$progressHandler` will be invoked and
passed a single argument (whatever it wants) to indicate progress.

Once in the resolved or rejected state, a Promise becomes immutable.
Neither its state nor its result (or error) can be modified.

A Promise makes the following guarantees about handlers registered in
the same call to `then()`:

  1. Only one of `$fulfilledHandler` or `$errorHandler` will be called,
     never both.
  2. `$fulfilledHandler` and `$errorHandler` will never be called more
     than once.
  3. `$progressHandler` may be called multiple times.

### Resolver

A Resolver has 3 methods: `resolve()`, `reject()` and `progress()`:

``` php
$resolver->resolve(mixed $result = null);
```

Resolves a Deferred. All consumers are notified by having their
`$fulfilledHandler` (which they registered via `$promise->then()`) called with
`$result`.

``` php
$resolver->reject(mixed $error = null);
```

Rejects a Deferred, signalling that the Deferred's computation failed.
All consumers are notified by having their `$errorHandler` (which they
registered via `$promise->then()`) called with `$error`.

``` php
$resolver->progress(mixed $update = null);
```

Triggers progress notifications, to indicate to consumers that the computation
is making progress toward its result.

All consumers are notified by having their `$progressHandler` (which they
registered via `$promise->then()`) called with `$update`.

### When

The `Promise\When` class provides useful methods for as joining, mapping and
reducing collections of promises.

#### When::all()

``` php
$promise = Promise\When::all(array|Promise\PromiseInterface $promisesOrValues, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

Returns a Promise that will resolve only once all the items in
`$promisesOrValues` have resolved. The resolution value of the returned Promise
will be an array containing the resolution values of each of the input array.

#### When::any()

``` php
$promise = Promise\When::any(array|Promise\PromiseInterface $promisesOrValues, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

Returns a Promise that will resolve when any one of the items in
`$promisesOrValues` has resolved. The resolution value of the returned Promise
will be the resolution value of the triggering item.

#### When::some()

``` php
$promise = Promise\When::some(array|Promise\PromiseInterface $promisesOrValues, integer $howMany, callable $fulfilledHandler = null, callable $errorHandler = null, callable $progressHandler = null);
```

Returns a Promise that will resolve when `$howMany` of the supplied items in
`$promisesOrValues` have resolved. The resolution value of the returned Promise
will be an array of length $howMany containing the resolutionsnvalues of the
triggering items.

#### When::map()

``` php
$promise = Promise\When::map(array|Promise\PromiseInterface $promisesOrValues, callable $mapFunc);
```

Traditional map function, similar to `array_map()`, but allows input to contain
Promises and/or values, and `$mapFunc` may return either a value or a Promise.

The map function receives each item as argument, where item is a fully resolved
value of a Promise or value in `$promisesOrValues`.

#### When::reduce()

``` php
$promise = Promise\When::reduce(array|Promise\PromiseInterface $promisesOrValues, callable $reduceFunc , $initialValue = null);
```

Traditional reduce function, similar to `array_reduce()`, but input may contain
Promises and/or values, and `$reduceFunc` may return either a value or a
Promise, *and* `$initialValue` may be a Promise or a value for the starting
value.

### Util

The `Promise\Util` class provides usefull methods for creating promises.

#### Util::resolve()

``` php
$promise = Promise\Util::resolve(mixed $promiseOrValue);
```

Creates a resolved Promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the resolution value of the
returned Promise.

If `$promiseOrValue` is a Promise, it will simply be returned.

#### Util::reject()

``` php
$promise = Promise\Util::reject(mixed $promiseOrValue);
```

Creates a rejected Promise for the supplied `$promiseOrValue`.

If `$promiseOrValue` is a value, it will be the rejection value of the
returned Promise.

If `$promiseOrValue` is a Promise, its completion value will be the rejected
value of the returned Promise.

This can be useful in situations where you need to reject a Promise without
throwing an exception. For example, it allows you to propagate a rejection with
the value of another Promise.

Examples
--------

### How to use Deferred

``` php
function getAwesomeResultPromise()
{
    $deferred = new Promise\Deferred();

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
        function ($error) {
            // Deferred rejected, do something with $error
        },
        function ($update) {
            // Progress notification triggered, do something with $update
        }
    );
```

### How Promise forwarding works

A few simple examples to show how the mechanics of Promises/A forwarding works.
These examples are contrived, of course, and in real usage, promise chains will
typically be spread across several function calls, or even several levels of
your application architecture.

#### Example 1

Resolved promises chain and forward values to next promise.
The first promise, `$deferred->promise()`, will resolve with the value passed
to `$deferred->resolve()` below.

Each call to `then()` returns a new promise that will resolve with the return
value of the previous handler. This creates a promise "pipeline".

``` php
$deferred = new Promise\Deferred();

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

#### Example 2

Rejected promises behave similarly, and also work similarly to try/catch:
When you catch an exception, you must rethrow for it to propagate.

Similarly, when you handle a rejected promise, to propagate the rejection,
"rethrow" it by either returning a rejected promise, or actually throwing
(since Promise translates thrown exceptions into rejections)

``` php
$deferred = new Promise\Deferred();

$deferred->promise()
    ->then(function ($x) {
        throw $x + 1;
    })
    ->then(null, function ($x) {
        // Propagate the rejection
        throw $x + 1;
    })
    ->then(null, function ($x) {
        // Can also propagate by returning another rejection
        return Promise\Util::reject(($x + 1);
    })
    ->then(null, function ($x) {
        echo 'Reject ' . $x; // 4
    });
    
$deferred->resolve(1);  // Reject "Resolve 4"
```

#### Example 3

Just like try/catch, you can choose to propagate or not. Mixing resolutions and
rejections will still forward handler results in a predictable way.

``` php
$deferred = new Promise\Deferred();

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
        return $x->getMessage() + 1;
    })
    ->then(function ($x) {
        echo 'Mixed ' . $x; // 4
    });
    
$deferred->resolve(1);  // Mixed "Resolve 4"
```

License
-------

Promise is released under the [MIT](https://github.com/jsor/promise/blob/master/LICENSE) license.
