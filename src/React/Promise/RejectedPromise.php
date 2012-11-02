<?php

namespace React\Promise;

class RejectedPromise implements PromiseInterface
{
    private $error;

    public function __construct($error = null)
    {
        $this->error = $error;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            if (!$errorHandler) {
                return new RejectedPromise($this->error);
            }

            return Util::promiseFor(call_user_func($errorHandler, $this->error));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
