<?php

namespace React\Promise;

class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
            $value = $this->value;

            if (null !== $onFulfilled) {
                $value = call_user_func($onFulfilled, $value);
            }

            return resolve($value);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
