<?php

namespace React\Promise;

class DeferredPromise implements PromiseInterface, CancellablePromiseInterface
{
    private $deferred;

    public function __construct(Deferred $deferred)
    {
        $this->deferred = $deferred;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function cancel()
    {
        $this->deferred->cancel();
    }
}
