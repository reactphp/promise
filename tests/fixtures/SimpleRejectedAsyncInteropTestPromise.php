<?php

namespace React\Promise;

use Interop\Async\Promise as AsyncInteropPromise;

class SimpleRejectedAsyncInteropTestPromise implements AsyncInteropPromise
{
    private $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function when(callable $onResolved)
    {
        $onResolved($this->exception, null);
    }
}
