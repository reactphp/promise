<?php

namespace React\Promise;

use Interop\Async\Promise as AsyncInteropPromise;

interface PromiseInterface extends AsyncInteropPromise
{
    /**
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);
}
