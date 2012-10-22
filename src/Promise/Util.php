<?php

namespace Promise;

/**
 * Utility class.
 */
class Util
{
    /**
     * Returns a trusted Promise for arbitary input.
     *
     * @param  mixed|PromiseInterface $promiseOrValue
     * @param  callable               $fulfilledHandler
     * @param  callable               $errorHandler
     * @param  callable               $progressHandler
     * @return PromiseInterface
     */
    public static function normalize($promiseOrValue, $fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return static::resolve($promiseOrValue)->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    /**
     * Create a resolved Promise for the supplied $promiseOrValue.
     *
     * If $promiseOrValue is a value, it will be the resolution value of the
     * returned Promise. Returns $promiseOrValue if it's a trusted Promise.
     *
     * If $promiseOrValue is a foreign Promise, returns a Promise in the same
     * state (resolved or rejected) and with the same value as $promiseOrValue.
     *
     * @param  mixed|PromiseInterface $promiseOrValue
     * @return PromiseInterface
     */
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

    /**
     * Create a rejected Promise for the supplied $promiseOrValue.
     *
     * If $promiseOrValue is a value, it will be the rejection value of the
     * returned Promise.
     *
     * If $promiseOrValue is a Promise, its completion value will be the
     * rejected value of the returned Promise.
     *
     * This can be useful in situations where you need to reject a Promise
     * without throwing an exception. For example, it allows you to propagate
     * a rejection with the value of another Promise.
     *
     * @param  mixed|PromiseInterface $promiseOrValue
     * @return PromiseInterface
     */
    public static function reject($promiseOrValue)
    {
        return static::normalize($promiseOrValue, function ($value = null) {
            return new RejectedPromise($value);
        });
    }

    /**
     * Returns whether $promiseOrValue looks like a Promise.
     *
     * @param  mixed   $promiseOrValue
     * @return boolean
     */
    public static function seemsPromise($promiseOrValue)
    {
        return is_object($promiseOrValue) && method_exists($promiseOrValue, 'then');
    }
}
