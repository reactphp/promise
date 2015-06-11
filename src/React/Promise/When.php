<?php

namespace React\Promise;

class When
{
    public static function resolve($promiseOrValue = null)
    {
        return resolve($promiseOrValue);
    }

    public static function reject($promiseOrValue = null)
    {
        return reject($promiseOrValue);
    }

    public static function lazy($factory)
    {
        return new LazyPromise($factory);
    }

    public static function all($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return all($promisesOrValues)->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public static function any($promisesOrValues, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return any($promisesOrValues)->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public static function some($promisesOrValues, $howMany, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return some($promisesOrValues, $howMany)->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public static function map($promisesOrValues, $mapFunc)
    {
        return map($promisesOrValues, $mapFunc);
    }

    public static function reduce($promisesOrValues, $reduceFunc , $initialValue = null)
    {
        return reduce($promisesOrValues, $reduceFunc, $initialValue);
    }
}
