<?php

namespace Promise;

class Util
{
    public static function normalize($promiseOrValue, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return static::resolve($promiseOrValue)->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public static function resolve($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return $promiseOrValue;
        }

        if (static::seemsPromise($promiseOrValue)) {
            $deferred = new Deferred();

            $promiseOrValue->then(
                array($deferred, 'resolve'),
                array($deferred, 'reject'),
                array($deferred, 'progress')
            );

            return $deferred->promise();
        }

        return new ResolvedPromise($promiseOrValue);
    }

    public static function reject($promiseOrValue)
    {
        return static::normalize($promiseOrValue, function ($value = null) {
            return new RejectedPromise($value);
        });
    }

    public static function seemsPromise($promiseOrValue)
    {
        return is_object($promiseOrValue) && method_exists($promiseOrValue, 'then');
    }
}
