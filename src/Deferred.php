<?php

namespace React\Promise;

class Deferred implements PromisorInterface
{
    private $promise;
    private $resolveCallback;
    private $rejectCallback;
    private $progressCallback;

    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new Promise(function ($resolve, $reject, $progress) {
                $this->resolveCallback  = $resolve;
                $this->rejectCallback   = $reject;
                $this->progressCallback = $progress;
            });
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        call_user_func($this->resolveCallback, $value);
    }

    public function reject($reason = null)
    {
        $this->promise();

        call_user_func($this->rejectCallback, $reason);
    }

    public function progress($update = null)
    {
        $this->promise();

        call_user_func($this->progressCallback, $update);
    }
}
