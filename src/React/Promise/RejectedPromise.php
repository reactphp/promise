<?php

namespace React\Promise;

class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
            if (null === $onRejected) {
                return new RejectedPromise($this->reason);
            }

            return resolve(call_user_func($onRejected, $this->reason));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
