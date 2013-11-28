<?php

namespace React\Promise;

function resolve($promiseOrValue = null)
{
    if ($promiseOrValue instanceof PromiseInterface) {
        return $promiseOrValue;
    }

    return new FulfilledPromise($promiseOrValue);
}

function reject($promiseOrValue = null)
{
    if ($promiseOrValue instanceof PromiseInterface) {
        return $promiseOrValue->then(function ($value) {
            return new RejectedPromise($value);
        });
    }

    return new RejectedPromise($promiseOrValue);
}

function all($promisesOrValues)
{
    return map($promisesOrValues, function ($val) {
        return $val;
    });
}

function any($promisesOrValues)
{
    return some($promisesOrValues, 1)
        ->then(function ($val) {
            return array_shift($val);
        });
}

function some($promisesOrValues, $howMany)
{
    return resolve($promisesOrValues)
        ->then(function ($array) use ($howMany) {
            if (!is_array($array) || !$array) {
                return resolve([]);
            }

            $len       = count($array);
            $toResolve = max(0, min($howMany, $len));
            $toReject  = ($len - $toResolve) + 1;
            $values    = [];
            $reasons   = [];
            $deferred  = new Deferred();

            $progress = [$deferred, 'progress'];

            $fulfillOne = function ($val, $i) use (&$values, &$toResolve, $deferred) {
                $values[$i] = $val;

                if (0 === --$toResolve) {
                    $deferred->resolve($values);

                    return true;
                }
            };

            $rejectOne = function ($reason, $i) use (&$reasons, &$toReject, $deferred) {
                $reasons[$i] = $reason;

                if (0 === --$toReject) {
                    $deferred->reject($reasons);

                    return true;
                }
            };

            foreach ($array as $i => $promiseOrValue) {
                $fulfiller = function ($val) use ($i, &$fulfillOne, &$rejectOne) {
                    $reset = $fulfillOne($val, $i);

                    if (true === $reset) {
                        $fulfillOne = $rejectOne = function () {};
                    }
                };

                $rejecter = function ($val) use ($i, &$fulfillOne, &$rejectOne) {
                    $reset = $rejectOne($val, $i);

                    if (true === $reset) {
                        $fulfillOne = $rejectOne = function () {};
                    }
                };

                resolve($promiseOrValue)
                    ->then($fulfiller, $rejecter, $progress);
            }

            return $deferred->promise();
        });
}

function map($promisesOrValues, callable $mapFunc)
{
    return resolve($promisesOrValues)
        ->then(function ($array) use ($mapFunc) {
            if (!is_array($array) || !$array) {
                return resolve([]);
            }

            $toResolve = count($array);
            $values    = [];
            $deferred  = new Deferred();

            $resolve = function ($item, $i) use ($mapFunc, &$values, &$toResolve, $deferred) {
                resolve($item)
                    ->then($mapFunc)
                    ->then(
                        function ($mapped) use (&$values, $i, &$toResolve, $deferred) {
                            $values[$i] = $mapped;

                            if (0 === --$toResolve) {
                                $deferred->resolve($values);
                            }
                        },
                        [$deferred, 'reject']
                    );
            };

            foreach ($array as $i => $item) {
                $resolve($item, $i);
            }

            return $deferred->promise();
        });
}

function reduce($promisesOrValues, callable $reduceFunc , $initialValue = null)
{
    return resolve($promisesOrValues)
        ->then(function ($array) use ($reduceFunc, $initialValue) {
            if (!is_array($array)) {
                $array = [];
            }

            $total = count($array);
            $i = 0;

            // Wrap the supplied $reduceFunc with one that handles promises and then
            // delegates to the supplied.
            $wrappedReduceFunc = function ($current, $val) use ($reduceFunc, $total, &$i) {
                return resolve($current)
                    ->then(function ($c) use ($reduceFunc, $total, &$i, $val) {
                        return resolve($val)
                            ->then(function ($value) use ($reduceFunc, $total, &$i, $c) {
                                return $reduceFunc($c, $value, $i++, $total);
                            });
                    });
            };

            return array_reduce($array, $wrappedReduceFunc, $initialValue);
        });
}
