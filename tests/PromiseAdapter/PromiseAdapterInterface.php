<?php

namespace React\Promise\PromiseAdapter;

use React\Promise\PromiseInterface;

interface PromiseAdapterInterface
{
    public function promise(): PromiseInterface;
    public function resolve(): void;
    public function reject(): void;
    public function settle(): void;
}
