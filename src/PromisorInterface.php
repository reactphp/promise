<?php

namespace React\Promise;

/** @psalm-template T */
interface PromisorInterface
{
    /**
     * Returns the promise of the deferred.
     *
     * @return PromiseInterface<T>
     */
    public function promise(): PromiseInterface;
}
