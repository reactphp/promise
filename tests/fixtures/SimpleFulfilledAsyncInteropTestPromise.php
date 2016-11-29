<?php

namespace React\Promise;

use Interop\Async\Promise as AsyncInteropPromise;

class SimpleFulfilledAsyncInteropTestPromise implements AsyncInteropPromise
{
    public function when(callable $onResolved)
    {
        $onResolved(null, 'foo');
    }
}
