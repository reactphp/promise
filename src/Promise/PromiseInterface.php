<?php

namespace Promise;

interface PromiseInterface
{
    /**
     * @param callable $fulfilledHandler
     * @param callable $errorHandler
     * @param callable $progressHandler
     *
     * @return PromiseInterface
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null);
}
