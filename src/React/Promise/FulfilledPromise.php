<?php

namespace React\Promise;

class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function then($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        try {
            $value = $this->value;

            if (is_callable($onFulfilled)) {
                $value = call_user_func($onFulfilled, $value);
            } elseif (null !== $onFulfilled) {
                trigger_error('Invalid $onFulfilled argument passed to then(), must be null or callable.', E_USER_NOTICE);
            }

            return Util::promiseFor($value);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
