<?php

namespace React\Promise;

class When
{
    public static function defer()
    {
        return new Deferred();
    }

    public static function resolve($promiseOrValue)
    {
        $deferred = static::defer();
        $deferred->resolve($promiseOrValue);

        return $deferred->promise();
    }

    public static function reject($promiseOrValue)
    {
        return static::resolve($promiseOrValue)->then(function ($value = null) {
            return new RejectedPromise($value);
        });
    }

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
        $deferredFactory = array(get_called_class(), 'defer');

        return When::resolve($promisesOrValues)->then(function ($array) use ($deferredFactory, $howMany, $fulfilledHandler, $errorHandler, $progressHandler) {
            if (!is_array($array)) {
                $array = array();
            }

            $len       = count($array);
            $toResolve = max(0, min($howMany, $len));
            $values    = array();
            $deferred  = call_user_func($deferredFactory);

            if (!$toResolve) {
                $deferred->resolve($values);
            } else {
                $toReject = ($len - $toResolve) + 1;
                $reasons  = array();

                $progress = array($deferred, 'progress');

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

                    When::resolve($promiseOrValue)->then($fulfiller, $rejecter, $progress);
                }
            }

            return $deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
        });
    }

    public static function map($promisesOrValues, $mapFunc)
    {
        $deferredFactory = array(get_called_class(), 'defer');

        return When::resolve($promisesOrValues)->then(function ($array) use ($deferredFactory, $mapFunc) {
            if (!is_array($array)) {
                $array = array();
            }

            $toResolve = count($array);
            $results   = array();
            $deferred  = call_user_func($deferredFactory);

            if (!$toResolve) {
                $deferred->resolve($results);
            } else {
                $resolve = function ($item, $i) use ($mapFunc, &$results, &$toResolve, $deferred) {
                    When::resolve($item)
                        ->then($mapFunc)
                        ->then(
                            function ($mapped) use (&$results, $i, &$toResolve, $deferred) {
                                $results[$i] = $mapped;

                                if (0 === --$toResolve) {
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
        return When::resolve($promisesOrValues)->then(function ($array) use ($reduceFunc, $initialValue) {
            if (!is_array($array)) {
                $array = array();
            }

            $total = count($array);
            $i = 0;

            // Wrap the supplied $reduceFunc with one that handles promises and then
            // delegates to the supplied.
            $wrappedReduceFunc = function ($current, $val) use ($reduceFunc, $total, &$i) {
                return When::resolve($current)->then(function ($c) use ($reduceFunc, $total, &$i, $val) {
                    return When::resolve($val)->then(function ($value) use ($reduceFunc, $total, &$i, $c) {
                        return call_user_func($reduceFunc, $c, $value, $i++, $total);
                    });
                });
            };

            return array_reduce($array, $wrappedReduceFunc, $initialValue);
        });
    }
}
