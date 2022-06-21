<?php

namespace React\Promise;

/** @template T */
interface PromiseInterface
{
    /**
     * @template TReturn of mixed
     * @param callable(T): TReturn $fulfilledHandler
     * @return (TReturn is PromiseInterface ? TReturn : PromiseInterface<TReturn>)
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null);
}
