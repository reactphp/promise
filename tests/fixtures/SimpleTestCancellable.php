<?php

namespace React\Promise;

class SimpleTestCancellable
{
    /** @var bool */
    public $cancelCalled = false;

    public function cancel(): void
    {
        $this->cancelCalled = true;
    }
}
