<?php

namespace React\Promise;

interface PromiseInterface
{
    /**
     * Transforms a promise's value by applying a function to the promise's fulfillment
     * or rejection value. Returns a new promise for the transformed result.
     *
     * The `then()` method registers new fulfilled and rejection handlers with a promise
     * (all parameters are optional):
     *
     *  * `$onFulfilled` will be invoked once the promise is fulfilled and passed
     *     the result as the first argument.
     *  * `$onRejected` will be invoked once the promise is rejected and passed the
     *     reason as the first argument.
     *
     * It returns a new promise that will fulfill with the return value of either
     * `$onFulfilled` or `$onRejected`, whichever is called, or will reject with
     * the thrown exception if either throws.
     *
     * A promise makes the following guarantees about handlers registered in
     * the same call to `then()`:
     *
     *  1. Only one of `$onFulfilled` or `$onRejected` will be called,
     *      never both.
     *  2. `$onFulfilled` and `$onRejected` will never be called more
     *      than once.
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * Consumes the promise's ultimate value if the promise fulfills, or handles the
     * ultimate error.
     *
     * It will cause a fatal error (`E_USER_ERROR`) if either `$onFulfilled` or
     * `$onRejected` throw or return a rejected promise.
     *
     * Since the purpose of `done()` is consumption rather than transformation,
     * `done()` always returns `null`.
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * Registers a rejection handler for promise. It is a shortcut for:
     *
     * ```php
     * $promise->then(null, $onRejected);
     * ```
     *
     * Additionally, you can type hint the `$reason` argument of `$onRejected` to catch
     * only specific errors.
     *
     * @param callable $onRejected
     * @return PromiseInterface
     */
    public function otherwise(callable $onRejected);

    /**
     * Allows you to execute "cleanup" type tasks in a promise chain.
     *
     * It arranges for `$onFulfilledOrRejected` to be called, with no arguments,
     * when the promise is either fulfilled or rejected.
     *
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will fulfill with the same value as `$promise`.
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will reject with the same reason as `$promise`.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     *
     * `always()` behaves similarly to the synchronous finally statement. When combined
     * with `otherwise()`, `always()` allows you to write code that is similar to the familiar
     * synchronous catch/finally pair.
     *
     * Consider the following synchronous code:
     *
     * ```php
     * try {
     *     return doSomething();
     * } catch(\Exception $e) {
     *     return handleError($e);
     * } finally {
     *     cleanup();
     * }
     * ```
     *
     * Similar asynchronous code (with `doSomething()` that returns a promise) can be
     * written:
     *
     * ```php
     * return doSomething()
     *     ->otherwise('handleError')
     *     ->always('cleanup');
     * ```
     *
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * The `cancel()` method notifies the creator of the promise that there is no
     * further interest in the results of the operation.
     *
     * Once a promise is settled (either fulfilled or rejected), calling `cancel()` on
     * a promise has no effect.
     *
     * @return void
     */
    public function cancel();
}
