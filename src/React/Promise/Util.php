<?php

namespace React\Promise;

class Util
{
    public static function promiseFor($promiseOrValue)
    {
        return resolve($promiseOrValue);
    }

    public static function rejectedPromiseFor($promiseOrValue)
    {
        return reject($promiseOrValue);
    }
}
