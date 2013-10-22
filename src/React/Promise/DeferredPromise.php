<?php

namespace React\Promise;

class DeferredPromise implements PromiseInterface
{
    private $thenCallback;

    public function __construct(callable $thenCallback)
    {
        $this->thenCallback = $thenCallback;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return call_user_func($this->thenCallback, $onFulfilled, $onRejected, $onProgress);
    }
}
