<?php

namespace React\Promise;

class FulfilledPromise implements PromiseInterface, CancellablePromiseInterface
{
    private $result;

    public function __construct($result = null)
    {
        $this->result = $result;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            $result = $this->result;

            if (is_callable($fulfilledHandler)) {
                $result = call_user_func($fulfilledHandler, $result);
            } elseif (null !== $fulfilledHandler) {
                trigger_error('Invalid $fulfilledHandler argument passed to then(), must be null or callable.', E_USER_NOTICE);
            }

            return resolve($result);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function cancel()
    {
    }
}
