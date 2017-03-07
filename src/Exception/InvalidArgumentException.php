<?php

namespace React\Promise\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    public static function invalidRejectionReason($reason)
    {
        return new self(
            sprintf(
                'A Promise must be rejected with a \Throwable or \Exception instance, got "%s" instead.',
                is_object($reason) ? get_class($reason) : gettype($reason)
            )
        );
    }
}
