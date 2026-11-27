<?php

namespace React\Promise;

/**
 * @template T
 */
interface DeferredInterface
{

    /**
     * @return PromiseInterface<T>
     */
    public function promise(): PromiseInterface;

    /**
     * @param T $value
     */
    public function resolve($value): void;

    public function reject(\Throwable $reason): void;

}