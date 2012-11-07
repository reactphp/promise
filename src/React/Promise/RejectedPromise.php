<?php

namespace React\Promise;

class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            if (!$errorHandler) {
                return new RejectedPromise($this->reason);
            }

            return Util::promiseFor(call_user_func($errorHandler, $this->reason));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
