<?php

namespace React\Promise;

use AsyncInterop\Promise as AsyncInteropPromise;

interface PromiseInterface extends AsyncInteropPromise
{
    /**
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return PromiseInterface
     */
    public function otherwise(callable $onRejected);

    /**
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * @return void
     */
    public function cancel();
}
