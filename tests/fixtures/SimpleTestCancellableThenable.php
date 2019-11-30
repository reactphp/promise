<?php

namespace React\Promise;

class SimpleTestCancellableThenable
{
    public $cancelCalled = false;
    public $onCancel;

    public function __construct(callable $onCancel = null)
    {
        $this->onCancel = $onCancel;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return new self();
    }

    public function cancel()
    {
        $this->cancelCalled = true;

        if (is_callable($this->onCancel)) {
            ($this->onCancel)();
        }
    }
}
