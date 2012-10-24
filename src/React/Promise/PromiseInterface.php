<?php

namespace React\Promise;

interface PromiseInterface
{
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null);
}
