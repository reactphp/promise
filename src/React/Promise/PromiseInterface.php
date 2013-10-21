<?php

namespace React\Promise;

interface PromiseInterface
{
    public function then($onFulfilled = null, $onRejected = null, $onProgress = null);
}
