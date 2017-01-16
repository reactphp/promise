<?php

namespace React\Promise\Exception;

class LogicException extends \LogicException
{
    public static function circularResolution()
    {
        return new self(
            'Cannot resolve a promise with itself.'
        );
    }

    public static function valueFromNonFulfilledPromise()
    {
        return new self(
            'Cannot get fulfillment value of a non-fulfilled promise.'
        );
    }

    public static function reasonFromNonRejectedPromise()
    {
        return new self(
            'Cannot get rejection reason of a non-rejected promise.'
        );
    }
}
