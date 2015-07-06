<?php

namespace React\Promise;

class SimpleRejectedTestPromise implements PromiseInterface
{
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
            if ($onRejected) {
                $onRejected('foo');
            }

            return new self('foo');
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
