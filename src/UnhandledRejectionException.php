<?php

namespace React\Promise;

class UnhandledRejectionException extends \RuntimeException
{
    private $reason;

    public static function resolve($reason)
    {
        if ($reason instanceof \Exception || $reason instanceof \Throwable) {
            return $reason;
        }

        return new static($reason);
    }

    public static function nullOrResolve($reason)
    {
        if (null === $reason) {
            return null;
        }

        return self::resolve($reason);
    }

    public function __construct($reason)
    {
        $this->reason = $reason;

        $message = sprintf('Unhandled Rejection: %s', json_encode($reason));

        parent::__construct($message, 0);
    }

    public function getReason()
    {
        return $this->reason;
    }
}
