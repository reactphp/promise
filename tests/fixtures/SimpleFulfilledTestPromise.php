<?php

namespace React\Promise;

class SimpleFulfilledTestPromise implements PromiseInterface
{
    public $cancelCalled = false;

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
            if ($onFulfilled) {
                $onFulfilled('foo');
            }

            return new self('foo');
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function cancel()
    {
        $this->cancelCalled = true;
    }
}
