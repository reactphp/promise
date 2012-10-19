<?php

namespace Promise;

class When
{
    public static function all(array $promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $promise = static::map($promisesOrValues, function ($val) {
            return $val;
        });

        return $promise->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function any($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $unwrapSingleResult = function ($val) use ($fulfilledHandler) {
            return $fulfilledHandler ? $fulfilledHandler($val[0]) : $val[0];
        };

        return static::some($promisesOrValues, 1, $unwrapSingleResult, $errorHandler, $progressHandler);
    }

    public static function some($promisesOrValues, $howMany, $fulfilledHandler, $errorHandler, $progressHandler)
    {
        return Util::normalize($promisesOrValues, function($promisesOrValues) use ($howMany, $fulfilledHandler, $errorHandler, $progressHandler) {
            $len       = count($promisesOrValues);
            $toResolve = max(0, min($howMany, $len));
            $results   = array();
            $deferred  = new Deferred();

            if (!$toResolve) {
                $deferred->resolve($results);
            } else {
                $reject   = array($deferred, 'reject');
                $progress = array($deferred, 'progress');

                foreach ($promisesOrValues as $i => $promisOrValue) {
                    $resolve = function($val) use ($i, &$results, &$toResolve, &$resolve, $deferred) {
                        $results[$i] = $val;

                        if (!--$toResolve) {
                            $resolve = function() {};
                            $deferred->resolve($results);
                        }
                    };

                    Util::normalize($promisOrValue, $resolve, $reject, $progress);
                }
            }

            return $deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
        });
    }

    public static function map($promise, $mapFunc)
    {
        return Util::normalize($promise, function($array) use ($mapFunc) {
            $toResolve = count($array);
            $results   = array();
            $deferred  = new Deferred();

            if (!$toResolve) {
                $deferred->resolve($results);
            } else {
                $resolve = function ($item, $i) use ($mapFunc, &$results, &$toResolve, $deferred) {
                    $promise = Util::normalize($item, $mapFunc);
                    $promise->then(function($mapped) use (&$results, $i, &$toResolve, $deferred) {
                        $results[$i] = $mapped;

                        if (!--$toResolve) {
                            $deferred->resolve($results);
                        }
                    }, array($deferred, 'reject'));
                };

                foreach ($array as $i => $item) {
                    $resolve($item, $i);
                }
            }

            return $deferred->promise();
        });
    }
}
