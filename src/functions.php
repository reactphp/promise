<?php

namespace React\Promise;

use React\Promise\Exception\CompositeException;
use React\Promise\Internal\FulfilledPromise;
use React\Promise\Internal\RejectedPromise;

/**
 * Creates a promise for the supplied `$promiseOrValue`.
 *
 * If `$promiseOrValue` is a value, it will be the resolution value of the
 * returned promise.
 *
 * If `$promiseOrValue` is a thenable (any object that provides a `then()` method),
 * a trusted promise that follows the state of the thenable is returned.
 *
 * If `$promiseOrValue` is a promise, it will be returned as is.
 *
 * @param mixed $promiseOrValue
 * @return PromiseInterface
 */

function resolve($promiseOrValue = null): PromiseInterface
{
    if ($promiseOrValue instanceof PromiseInterface) {
        return $promiseOrValue;
    }

    if (\is_object($promiseOrValue) && \method_exists($promiseOrValue, 'then')) {
        $canceller = null;

        if (\method_exists($promiseOrValue, 'cancel')) {
            $canceller = [$promiseOrValue, 'cancel'];
        }

        return new Promise(function ($resolve, $reject) use ($promiseOrValue): void {
            $promiseOrValue->then($resolve, $reject);
        }, $canceller);
    }

    return new FulfilledPromise($promiseOrValue);
}

/**
 * Creates a rejected promise for the supplied `$reason`.
 *
 * If `$reason` is a value, it will be the rejection value of the
 * returned promise.
 *
 * If `$reason` is a promise, its completion value will be the rejected
 * value of the returned promise.
 *
 * This can be useful in situations where you need to reject a promise without
 * throwing an exception. For example, it allows you to propagate a rejection with
 * the value of another promise.
 *
 * @param \Throwable $reason
 * @return PromiseInterface
 */
function reject(\Throwable $reason): PromiseInterface
{
    return new RejectedPromise($reason);
}

/**
 * Returns a promise that will resolve only once all the items in
 * `$promisesOrValues` have resolved. The resolution value of the returned promise
 * will be an array containing the resolution values of each of the items in
 * `$promisesOrValues`.
 *
 * @param array $promisesOrValues
 * @return PromiseInterface
 */
function all(array $promisesOrValues): PromiseInterface
{
    return map($promisesOrValues, function ($val) {
        return $val;
    });
}

/**
 * Initiates a competitive race that allows one winner. Returns a promise which is
 * resolved in the same way the first settled promise resolves.
 *
 * @param PromiseInterface $promise
 * @param array<PromiseInterface> $promises
 * @return PromiseInterface
 */
function race(PromiseInterface $promise, PromiseInterface ...$promises): PromiseInterface
{
    $promises[] = $promise;
    $cancellationQueue = new Internal\CancellationQueue();

    return new Promise(function ($resolve, $reject) use ($promises, $cancellationQueue): void {
        foreach ($promises as $promiseOrValue) {
            $cancellationQueue->enqueue($promiseOrValue);

            resolve($promiseOrValue)
                ->done($resolve, $reject);
        }
    }, $cancellationQueue);
}

/**
 * Returns a promise that will resolve when any one of the items in
 * `$promisesOrValues` resolves. The resolution value of the returned promise
 * will be the resolution value of the triggering item.
 *
 * The returned promise will only reject if *all* items in `$promisesOrValues` are
 * rejected. The rejection value will be an array of all rejection reasons.
 *
 * The returned promise will also reject with a `React\Promise\Exception\LengthException`
 * if `$promisesOrValues` contains 0 items.
 *
 * @param array $promisesOrValues
 * @return PromiseInterface
 */
function any(array $promisesOrValues): PromiseInterface
{
    return some($promisesOrValues, 1)
        ->then(function ($val) {
            return \array_shift($val);
        });
}

/**
 * Returns a promise that will resolve when `$howMany` of the supplied items in
 * `$promisesOrValues` resolve. The resolution value of the returned promise
 * will be an array of length `$howMany` containing the resolution values of the
 * triggering items.
 *
 * The returned promise will reject if it becomes impossible for `$howMany` items
 * to resolve (that is, when `(count($promisesOrValues) - $howMany) + 1` items
 * reject). The rejection value will be an array of
 * `(count($promisesOrValues) - $howMany) + 1` rejection reasons.
 *
 * The returned promise will also reject with a `React\Promise\Exception\LengthException`
 * if `$promisesOrValues` contains less items than `$howMany`.
 *
 * @param array $promisesOrValues
 * @param int $howMany
 * @return PromiseInterface
 */
function some(array $promisesOrValues, int $howMany): PromiseInterface
{
    if ($howMany < 1) {
        return resolve([]);
    }

    $len = \count($promisesOrValues);

    if ($len < $howMany) {
        return reject(
            new Exception\LengthException(
                \sprintf(
                    'Input array must contain at least %d item%s but contains only %s item%s.',
                    $howMany,
                    1 === $howMany ? '' : 's',
                    $len,
                    1 === $len ? '' : 's'
                )
            )
        );
    }

    $cancellationQueue = new Internal\CancellationQueue();

    return new Promise(function ($resolve, $reject) use ($len, $promisesOrValues, $howMany, $cancellationQueue): void {
        $toResolve = $howMany;
        $toReject  = ($len - $toResolve) + 1;
        $values    = [];
        $reasons   = [];

        foreach ($promisesOrValues as $i => $promiseOrValue) {
            $fulfiller = function ($val) use ($i, &$values, &$toResolve, $toReject, $resolve): void {
                if ($toResolve < 1 || $toReject < 1) {
                    return;
                }

                $values[$i] = $val;

                if (0 === --$toResolve) {
                    $resolve($values);
                }
            };

            $rejecter = function (\Throwable $reason) use ($i, &$reasons, &$toReject, $toResolve, $reject): void {
                if ($toResolve < 1 || $toReject < 1) {
                    return;
                }

                $reasons[$i] = $reason;

                if (0 === --$toReject) {
                    $reject(
                        new CompositeException(
                            $reasons,
                            'Too many promises rejected.'
                        )
                    );
                }
            };

            $cancellationQueue->enqueue($promiseOrValue);

            resolve($promiseOrValue)
                ->done($fulfiller, $rejecter);
        }
    }, $cancellationQueue);
}

/**
 * Traditional map function, similar to `array_map()`, but allows input to contain
 * promises and/or values, and `$mapFunc` may return either a value or a promise.
 *
 * The map function receives each item as argument, where item is a fully resolved
 * value of a promise or value in `$promisesOrValues`.
 *
 * @param array $promisesOrValues
 * @param callable $mapFunc
 * @return PromiseInterface
 */
function map(array $promisesOrValues, callable $mapFunc): PromiseInterface
{
    if (!$promisesOrValues) {
        return resolve([]);
    }

    $cancellationQueue = new Internal\CancellationQueue();

    return new Promise(function ($resolve, $reject) use ($promisesOrValues, $mapFunc, $cancellationQueue): void {
        $toResolve = \count($promisesOrValues);
        $values    = [];

        foreach ($promisesOrValues as $i => $promiseOrValue) {
            $cancellationQueue->enqueue($promiseOrValue);
            $values[$i] = null;

            resolve($promiseOrValue)
                ->then($mapFunc)
                ->done(
                    function ($mapped) use ($i, &$values, &$toResolve, $resolve): void {
                        $values[$i] = $mapped;

                        if (0 === --$toResolve) {
                            $resolve($values);
                        }
                    },
                    $reject
                );
        }
    }, $cancellationQueue);
}

/**
 * Traditional reduce function, similar to `array_reduce()`, but input may contain
 * promises and/or values, and `$reduceFunc` may return either a value or a
 * promise, *and* `$initialValue` may be a promise or a value for the starting
 * value.
 *
 * @param array $promisesOrValues
 * @param callable $reduceFunc
 * @param mixed $initialValue
 * @return PromiseInterface
 */
function reduce(array $promisesOrValues, callable $reduceFunc, $initialValue = null): PromiseInterface
{
    $cancellationQueue = new Internal\CancellationQueue();

    return new Promise(function ($resolve, $reject) use ($promisesOrValues, $reduceFunc, $initialValue, $cancellationQueue): void {
        $total = \count($promisesOrValues);
        $i = 0;

        $wrappedReduceFunc = function ($current, $val) use ($reduceFunc, $cancellationQueue, $total, &$i): PromiseInterface {
            $cancellationQueue->enqueue($val);

            return $current
                ->then(function ($c) use ($reduceFunc, $total, &$i, $val) {
                    return resolve($val)
                        ->then(function ($value) use ($reduceFunc, $total, &$i, $c) {
                            return $reduceFunc($c, $value, $i++, $total);
                        });
                });
        };

        $cancellationQueue->enqueue($initialValue);

        \array_reduce($promisesOrValues, $wrappedReduceFunc, resolve($initialValue))
            ->done($resolve, $reject);
    }, $cancellationQueue);
}

/**
 * @internal
 */
function enqueue(callable $task): void
{
    static $queue;

    if (!$queue) {
        $queue = new Internal\Queue();
    }

    $queue->enqueue($task);
}

/**
 * @internal
 */
function fatalError($error): void
{
    try {
        \trigger_error($error, E_USER_ERROR);
    } catch (\Throwable $e) {
        \set_error_handler(null);
        \trigger_error($error, E_USER_ERROR);
    }
}

/**
 * @internal
 */
function _checkTypehint(callable $callback, \Throwable $reason): bool
{
    if (\is_array($callback)) {
        $callbackReflection = new \ReflectionMethod($callback[0], $callback[1]);
    } elseif (\is_object($callback) && !$callback instanceof \Closure) {
        $callbackReflection = new \ReflectionMethod($callback, '__invoke');
    } else {
        $callbackReflection = new \ReflectionFunction($callback);
    }

    $parameters = $callbackReflection->getParameters();

    if (!isset($parameters[0])) {
        return true;
    }

    $type = $parameters[0]->getType();

    if (!$type) {
        return true;
    }

    $types = [$type];

    if ($type instanceof \ReflectionUnionType) {
        $types = $type->getTypes();
    }

    $mismatched = false;

    foreach ($types as $type) {
        if (!$type || $type->isBuiltin()) {
            continue;
        }

        $expectedClass = $type->getName();

        if ($reason instanceof $expectedClass) {
            return true;
        }

        $mismatched = true;
    }

    return !$mismatched;
}
