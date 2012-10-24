<?php

namespace React\Promise;

class Util
{
    public static function resolve($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return $promiseOrValue;
        }

        return new ResolvedPromise($promiseOrValue);
    }

    public static function reject($promiseOrValue)
    {
        return static::resolve($promiseOrValue)->then(function ($value = null) {
            return new RejectedPromise($value);
        });
    }
}
