<?php

namespace React\Promise;

class FulfilledPromise implements ExtendedPromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        try {
            $value = $this->value;

            if (null !== $onFulfilled) {
                $value = $onFulfilled($value);
            }

            return resolve($value);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return;
        }

        $result = $onFulfilled($this->value);

        if ($result instanceof ExtendedPromiseInterface) {
            $result->done();
        }
    }

    public function progress(callable $onProgress)
    {
        return new FulfilledPromise($this->value);
    }
}
