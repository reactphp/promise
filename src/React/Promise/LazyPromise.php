<?php

namespace React\Promise;

class LazyPromise implements PromiseInterface
{
    private $factory;
    private $promise;

    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        if (null === $this->promise) {
            try {
                $this->promise = Util::promiseFor(call_user_func($this->factory));
            } catch (\Exception $exception) {
                $this->promise = new RejectedPromise($exception);
            }
        }

        return $this->promise->then($fulfilledHandler, $errorHandler, $progressHandler);
    }
}
