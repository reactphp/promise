<?php

namespace React\Promise;

use React\Promise\Exception\LogicException;

final class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
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
            enqueue(function () use ($resolve, $reject, $onFulfilled) {
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

        enqueue(function () use ($onFulfilled) {
            try {
                $result = $onFulfilled($this->value);
            } catch (\Throwable $exception) {
                return fatalError($exception);
            } catch (\Exception $exception) {
                return fatalError($exception);
            }

            if ($result instanceof PromiseInterface) {
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

    public function isFulfilled()
    {
        return true;
    }

    public function isRejected()
    {
        return false;
    }

    public function isPending()
    {
        return false;
    }

    public function isCancelled()
    {
        return false;
    }

    public function value()
    {
        return $this->value;
    }

    public function reason()
    {
        throw LogicException::reasonFromNonRejectedPromise();
    }
}
