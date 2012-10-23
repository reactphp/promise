<?php

namespace Promise;

class When
{
    public static function all($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $promise = static::map($promisesOrValues, function ($val) {
            return $val;
        });

        return $promise->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public static function any($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $unwrapSingleResult = function ($val) use ($fulfilledHandler) {
            $val = isset($val[0]) ? $val[0] : null;

            return $fulfilledHandler ? $fulfilledHandler($val) : $val;
        };

        return static::some($promisesOrValues, 1, $unwrapSingleResult, $errorHandler, $progressHandler);
    }

    public static function some($promisesOrValues, $howMany, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return Util::resolve($promisesOrValues)->then(function ($array) use ($howMany, $fulfilledHandler, $errorHandler, $progressHandler) {
            if (!is_array($array)) {
                $array = array();
            }

            $len       = count($array);
            $toResolve = max(0, min($howMany, $len));
            $results   = array();
            $deferred  = new Deferred();

            if (!$toResolve) {
                $deferred->resolve($results);
            } else {
                $reject   = array($deferred, 'reject');
                $progress = array($deferred, 'progress');

                foreach ($array as $i => $promiseOrValue) {
                    $resolve = function ($val) use ($i, &$results, &$toResolve, &$resolve, $deferred) {
                        $results[$i] = $val;

                        if (!--$toResolve) {
                            $resolve = function () {};
                            $deferred->resolve($results);
                        }
                    };

                    Util::resolve($promiseOrValue)->then($resolve, $reject, $progress);
                }
            }

            return $deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
        });
    }

    public static function map($promisesOrValues, $mapFunc)
    {
        return Util::resolve($promisesOrValues)->then(function ($array) use ($mapFunc) {
            if (!is_array($array)) {
                $array = array();
            }

            $toResolve = count($array);
            $results   = array();
            $deferred  = new Deferred();

            if (!$toResolve) {
                $deferred->resolve($results);
            } else {
                $resolve = function ($item, $i) use ($mapFunc, &$results, &$toResolve, $deferred) {
                    Util::resolve($item)
                        ->then($mapFunc)
                        ->then(
                            function ($mapped) use (&$results, $i, &$toResolve, $deferred) {
                                $results[$i] = $mapped;

                                if (!--$toResolve) {
                                    $deferred->resolve($results);
                                }
                            },
                            array($deferred, 'reject')
                        );
                };

                foreach ($array as $i => $item) {
                    $resolve($item, $i);
                }
            }

            return $deferred->promise();
        });
    }

    public static function reduce($promisesOrValues, $reduceFunc , $initialValue = null)
    {
        return Util::resolve($promisesOrValues)->then(function ($array) use ($reduceFunc, $initialValue) {
            if (!is_array($array)) {
                $array = array();
            }

            $total = count($array);
            $i = 0;

            // Wrap the supplied $reduceFunc with one that handles promises and then
            // delegates to the supplied.
            $wrappedReduceFunc = function ($current, $val) use ($reduceFunc, $total, &$i) {
                return Util::resolve($current)->then(function ($c) use ($reduceFunc, $total, &$i, $val) {
                    return Util::resolve($val)->then(function ($value) use ($reduceFunc, $total, &$i, $c) {
                        return call_user_func($reduceFunc, $c, $value, $i++, $total);
                    });
                });
            };

            return array_reduce($array, $wrappedReduceFunc, $initialValue);
        });
    }
}
