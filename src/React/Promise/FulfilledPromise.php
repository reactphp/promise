<?php

namespace React\Promise;

class FulfilledPromise implements PromiseInterface
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
            if ($fulfilledHandler) {
                $result = call_user_func($fulfilledHandler, $result);
            }

            return Util::resolve($result);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
