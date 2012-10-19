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
        if ($promiseOrValue instanceof Promise) {
            return $promiseOrValue;
        }

        if (static::isPromise($promiseOrValue)) {
            $deferred = new Deferred();

            $promiseOrValue->then(
                array($deferred, 'resolve'),
                array($deferred, 'reject'),
                array($deferred, 'progress')
            );

            return $deferred->promise();
        }

        return self::resolved($promiseOrValue);
    }

    public static function reject($promiseOrValue)
    {
        return static::normalize($promiseOrValue, function($value = null) {
            return Util::rejected($value);
        });
    }

    public static function resolved($value)
    {
        return new Promise(function($fulfilledHandler = null) use ($value) {
            try {
                if (is_callable($value)) {
                    $value = call_user_func($value);
                }

                return Util::resolve($fulfilledHandler ? call_user_func($fulfilledHandler, $value) : $value);
            } catch (\Exception $e) {
                return Util::rejected($e);
            }
        });
    }

    public static function rejected($reason)
    {
        return new Promise(function($fulfilledHandler = null, $errorHandler = null) use ($reason) {
            try {
                return $errorHandler ? Util::resolve(call_user_func($errorHandler, $reason)) : Util::rejected($reason);
            } catch (\Exception $e) {
                return Util::rejected($e);
            }
        });
    }

    public static function isPromise($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return true;
        }

        return is_object($promiseOrValue) && method_exists($promiseOrValue, 'then');
    }
}
