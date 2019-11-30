<?php

namespace React\Promise;

final class Deferred implements PromisorInterface
{
    private $promise;
    private $resolveCallback;
    private $rejectCallback;
    private $canceller;

    public function __construct(callable $canceller = null)
    {
        $this->canceller = $canceller;
    }

    public function promise(): PromiseInterface
    {
        if (null === $this->promise) {
            $canceller = $this->canceller;
            $this->canceller = null;

            $this->promise = new Promise(function ($resolve, $reject): void {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
            }, $canceller);
        }

        return $this->promise;
    }

    public function resolve($value = null): void
    {
        $this->promise();

        ($this->resolveCallback)($value);
    }

    public function reject(\Throwable $reason): void
    {
        $this->promise();

        ($this->rejectCallback)($reason);
    }
}
