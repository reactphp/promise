<?php

namespace React\Promise;

/**
 * This interface is only kept for backward compatibility and must not be used
 * anymore.
 *
 * @deprecated
 */
interface CancellablePromiseInterface
{
    /**
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return void
     */
    public function cancel();
}
