<?php

namespace React\Promise\PromiseAdapter;

use React\Promise\PromiseInterface;

/**
 * @template T
 */
interface PromiseAdapterInterface
{
    /**
     * @return PromiseInterface<T>
     */
    public function promise(): PromiseInterface;

    /**
     * @param mixed $value
     */
    public function resolve($value): void;
    public function reject(): void;
    public function settle(): void;
}
