<?php

namespace React\Promise;

class UnhandledRejectionException extends \RuntimeException
{
    private $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;

        $message = sprintf(
            'Unhandled Rejection: %s',
            $reason instanceof \Exception ? $reason->getMessage() : json_encode($reason)
        );

        $previous = $reason instanceof \Exception ? $reason : null;

        parent::__construct($message, 0, $previous);
    }

    public function getReason()
    {
        return $this->reason;
    }
}
