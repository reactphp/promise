<?php

namespace React\Promise;

class SimpleTestCancellableThenable
{
    /** @var bool */
    public $cancelCalled = false;

    /** @var ?callable */
    public $onCancel;

    public function __construct(?callable $onCancel = null)
    {
        $this->onCancel = $onCancel;
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): self
    {
        return new self();
    }

    public function cancel(): void
    {
        $this->cancelCalled = true;

        if (is_callable($this->onCancel)) {
            ($this->onCancel)();
        }
    }
}
