<?php

namespace React\Promise;

use AsyncInterop\Promise as AsyncInteropPromise;

class SimpleFulfilledAsyncInteropTestPromise implements AsyncInteropPromise
{
    public function when(callable $onResolved)
    {
        $onResolved(null, 'foo');
    }
}
