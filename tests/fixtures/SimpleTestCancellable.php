<?php

namespace React\Promise;

class SimpleTestCancellable
{
    public $cancelCalled = false;

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
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
