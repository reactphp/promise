<?php

namespace React\Promise;

class Promise implements PromiseInterface, CancellablePromiseInterface
{
    private $deferred;

    public function __construct($resolver, $canceller = null)
    {
        if (!is_callable($resolver)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The resolver argument must be of type callable, %s given.',
                    gettype($resolver)
                )
            );
        }

        $this->deferred = new Deferred($canceller);
        $this->call($resolver);
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function cancel()
    {
        $this->deferred->cancel();
    }

    private function call($callback)
    {
        $deferred = $this->deferred;

        try {
            call_user_func(
                $callback,
                function ($result = null) use ($deferred) {
                    $deferred->resolve($result);
                },
                function ($reason = null) use ($deferred) {
                    $deferred->reject($reason);
                },
                function ($update = null) use ($deferred) {
                    $deferred->progress($update);
                }
            );
        } catch (\Exception $e) {
            $this->deferred->reject($e);
        }
    }
}
