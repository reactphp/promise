<?php

namespace React\Promise;

class Util
{
    public static function promiseFor($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return $promiseOrValue;
        }

        return new FulfilledPromise($promiseOrValue);
    }

    public static function rejectedPromiseFor($promiseOrValue)
    {
        if ($promiseOrValue instanceof PromiseInterface) {
            return $promiseOrValue->then(function ($value) {
                return new RejectedPromise($value);
            });
        }

        return new RejectedPromise($promiseOrValue);
    }
}
