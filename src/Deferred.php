<?php

namespace React\Promise;

final class Deferred
{
    /** @var Promise */
    private $promise;

    /** @var callable */
    private $resolveCallback;

    /** @var callable */
    private $rejectCallback;

    public function __construct(callable $canceller = null)
    {
        $this->promise = new Promise(function ($resolve, $reject): void {
            $this->resolveCallback = $resolve;
            $this->rejectCallback  = $reject;
        }, $canceller);
    }

    public function promise(): PromiseInterface
    {
        return $this->promise;
    }

    /**
     * @param mixed $value
     */
    public function resolve($value): void
    {
        ($this->resolveCallback)($value);
    }

    public function reject(\Throwable $reason): void
    {
        ($this->rejectCallback)($reason);
    }
}
