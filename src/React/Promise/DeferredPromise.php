<?php

namespace React\Promise;

class DeferredPromise implements PromiseInterface
{
    private $deferred;

    public function __construct(Deferred $deferred)
    {
        $this->deferred = $deferred;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->deferred->then($onFulfilled, $onRejected, $onProgress);
    }
}
