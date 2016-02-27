<?php

namespace React\Promise;

class LazyPromise implements PromiseInterface, CancellablePromiseInterface
{
    private $factory;
    private $promise;

    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->promise()->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function cancel()
    {
        $promise = $this->promise();
        if ($promise instanceof CancellablePromiseInterface) {
            $promise->cancel();
        }
    }

    private function promise()
    {
        if (null === $this->promise) {
            try {
                $this->promise = resolve(call_user_func($this->factory));
            } catch (\Exception $exception) {
                $this->promise = new RejectedPromise($exception);
            }
        }
        return $this->promise;
    }
}
