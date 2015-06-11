<?php

namespace React\Promise;

class Promise implements PromiseInterface
{
    private $deferred;

    public function __construct($resolver, $canceller = null)
    {
        if (!is_callable($resolver)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The resolver arguments must be of type callable, %s given.',
                    gettype($resolver)
                )
            );
        }

        $this->deferred = new Deferred();
        $this->call($resolver);
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    private function call($callback)
    {
        try {
            call_user_func(
                $callback,
                array($this->deferred, 'resolve'),
                array($this->deferred, 'reject'),
                array($this->deferred, 'progress')
            );
        } catch (\Exception $e) {
            $this->deferred->reject($e);
        }
    }
}
