<?php

namespace Promise;

/**
 * Utility class.
 */
class Util
{
    /**
     * Create a resolved Promise for the supplied $promiseOrValue.
     *
     * If $promiseOrValue is a value, it will be the resolution value of the
     * returned Promise.
     *
     * If $promiseOrValue is a Promise, it will be returned.
     *
     * @param  mixed|PromiseInterface $promiseOrValue
     * @return PromiseInterface
     */
    public static function resolve($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return $promiseOrValue;
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
        return static::resolve($promiseOrValue)->then(function ($value = null) {
            return new RejectedPromise($value);
        });
    }
}
