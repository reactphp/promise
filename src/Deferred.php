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

    public function promise()
    {
        if (null === $this->promise) {
            $canceller = $this->canceller;
            $this->canceller = null;

            $this->promise = new Promise(function ($resolve, $reject) {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
            }, $canceller);
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        \call_user_func($this->resolveCallback, $value);
    }

    public function reject($reason)
    {
        $this->promise();

        \call_user_func($this->rejectCallback, $reason);
    }
}
