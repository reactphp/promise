<?php

namespace Promise;

class When
{
    /**
     * Return a Promise that will resolve only once all the items in
     * $promisesOrValues have resolved. The resolution value of the returned
     * Promise will be an array containing the resolution values of each of
     * the input array.
     *
     * @param  array|PromiseInterface $promisesOrValues Array or a Promise for an array, which may contain Promises and/or values.
     * @param  callable               $fulfilledHandler
     * @param  callable               $errorHandler
     * @param  callable               $progressHandler
     * @return PromiseInterface
     */
    public static function all($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $promise = static::map($promisesOrValues, function ($val) {
            return $val;
        });

        return $promise->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    /**
     * Return a Promise that will resolve when any one of the items in
     * $promisesOrValues has resolved. The resolution value of the returned
     * Promise will be the resolution value of the triggering item.
     *
     * @param  array|PromiseInterface $promisesOrValues Array or a Promise for an array, which may contain Promises and/or values.
     * @param  callable               $fulfilledHandler
     * @param  callable               $errorHandler
     * @param  callable               $progressHandler
     * @return PromiseInterface
     */
    public static function any($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $unwrapSingleResult = function ($val) use ($fulfilledHandler) {
            $val = isset($val[0]) ? $val[0] : null;

            return $fulfilledHandler ? $fulfilledHandler($val) : $val;
        };

        return static::some($promisesOrValues, 1, $unwrapSingleResult, $errorHandler, $progressHandler);
    }

    /**
     * Return a Promise that will resolve when $howMany of the supplied items
     * in $promisesOrValues have resolved. The resolution value of the returned
     * Promise will be an array of length $howMany containing the resolutions
     * values of the triggering items.
     *
     * @param  array|PromiseInterface $promisesOrValues Array or a Promise for an array, which may contain Promises and/or values.
     * @param  callable               $fulfilledHandler
     * @param  callable               $errorHandler
     * @param  callable               $progressHandler
     * @return PromiseInterface
     */
    public static function some($promisesOrValues, $howMany, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return Util::normalize($promisesOrValues, function ($array) use ($howMany, $fulfilledHandler, $errorHandler, $progressHandler) {
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

                    Util::normalize($promiseOrValue, $resolve, $reject, $progress);
                }
            }

            return $deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
        });
    }

    /**
     * Traditional map function, similar to array_map, but allows input to
     * contain Promises and/or values, and $mapFunc may return either a value
     * or a Promise.
     *
     * The map function receives each item as argument, where item is a fully
     * resolved value of a Promise or value in $promisesOrValues.
     *
     * @param  array|PromiseInterface $promisesOrValues Array or a Promise for an array, which may contain Promises and/or values.
     * @param  callable               $fulfilledHandler
     * @param  callable               $errorHandler
     * @param  callable               $progressHandler
     * @return PromiseInterface
     */
    public static function map($promisesOrValues, $mapFunc)
    {
        return Util::normalize($promisesOrValues, function ($array) use ($mapFunc) {
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
                    $promise = Util::normalize($item, $mapFunc);
                    $promise->then(function ($mapped) use (&$results, $i, &$toResolve, $deferred) {
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
