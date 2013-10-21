<?php

namespace React\Promise;

class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function then($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        try {
            if (!is_callable($onRejected)) {
                if (null !== $onRejected) {
                    trigger_error('Invalid $onRejected argument passed to then(), must be null or callable.', E_USER_NOTICE);
                }

                return new RejectedPromise($this->reason);
            }

            return Util::promiseFor(call_user_func($onRejected, $this->reason));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
