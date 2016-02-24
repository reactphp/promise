<?php

namespace React\Promise;

class RejectedPromise implements PromiseInterface, CancellablePromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            if (!is_callable($errorHandler)) {
                if (null !== $errorHandler) {
                    trigger_error('Invalid $errorHandler argument passed to then(), must be null or callable.', E_USER_NOTICE);
                }

                return new RejectedPromise($this->reason);
            }

            return resolve(call_user_func($errorHandler, $this->reason));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function cancel()
    {
    }
}
