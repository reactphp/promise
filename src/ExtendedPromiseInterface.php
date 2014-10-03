<?php

namespace React\Promise;

interface ExtendedPromiseInterface extends PromiseInterface
{
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null);
    public function otherwise(callable $onRejected);
    public function progress(callable $onProgress);
}
