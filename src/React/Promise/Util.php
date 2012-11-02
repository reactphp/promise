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
}
