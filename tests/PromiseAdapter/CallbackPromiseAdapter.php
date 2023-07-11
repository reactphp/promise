<?php

namespace React\Promise\PromiseAdapter;

use React\Promise\PromiseInterface;

/**
 * @template T
 * @template-implements PromiseAdapterInterface<T>
 */
class CallbackPromiseAdapter implements PromiseAdapterInterface
{
    /** @var callable[] */
    private $callbacks;

    /**
     * @param callable[] $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    /**
     * @return PromiseInterface<T>
     */
    public function promise(): PromiseInterface
    {
        return ($this->callbacks['promise'])(...func_get_args());
    }

    public function resolve($value): void
    {
        ($this->callbacks['resolve'])(...func_get_args());
    }

    public function reject(): void
    {
        ($this->callbacks['reject'])(...func_get_args());
    }

    public function settle(): void
    {
        ($this->callbacks['settle'])(...func_get_args());
    }
}
