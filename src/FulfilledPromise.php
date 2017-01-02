<?php

namespace React\Promise;

use Interop\Async\Promise as AsyncInteropPromise;

class FulfilledPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof AsyncInteropPromise) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onFulfilled) {
            queue()->enqueue(function () use ($resolve, $reject, $onFulfilled) {
                try {
                    $resolve($onFulfilled($this->value));
                } catch (\Throwable $exception) {
                    $reject($exception);
                } catch (\Exception $exception) {
                    $reject($exception);
                }
            });
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            return;
        }

        queue()->enqueue(function () use ($onFulfilled) {
            $result = $onFulfilled($this->value);

            if ($result instanceof ExtendedPromiseInterface) {
                $result->done();
            }
        });
    }

    public function otherwise(callable $onRejected)
    {
        return $this;
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        });
    }

    public function cancel()
    {
    }

    public function when(callable $onResolved)
    {
        try {
            $onResolved(null, $this->value);
        } catch (\Throwable $exception) {
            AsyncInteropPromise\ErrorHandler::notify($exception);
        } catch (\Exception $exception) {
            AsyncInteropPromise\ErrorHandler::notify($exception);
        }
    }
}
