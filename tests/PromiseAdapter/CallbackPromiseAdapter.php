<?php

namespace React\Promise\PromiseAdapter;

use React\Promise\PromiseInterface;

class CallbackPromiseAdapter implements PromiseAdapterInterface
{
    private $callbacks;

    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    public function promise(): ?PromiseInterface
    {
        return call_user_func_array($this->callbacks['promise'], func_get_args());
    }

    public function resolve(): ?PromiseInterface
    {
        return call_user_func_array($this->callbacks['resolve'], func_get_args());
    }

    public function reject(): ?PromiseInterface
    {
        return call_user_func_array($this->callbacks['reject'], func_get_args());
    }

    public function settle(): ?PromiseInterface
    {
        return call_user_func_array($this->callbacks['settle'], func_get_args());
    }
}
