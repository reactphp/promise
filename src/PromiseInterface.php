<?php

namespace React\Promise;

use AsyncInterop\Promise as AsyncInteropPromise;

interface PromiseInterface extends AsyncInteropPromise
{
    /**
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);
}
