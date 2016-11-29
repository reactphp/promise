<?php

namespace React\Promise;

use Interop\Async\Promise as AsyncInteropPromise;

class RejectedPromise implements ExtendedPromiseInterface, CancellablePromiseInterface, AsyncInteropPromise
{
    private $reason;

    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface || $reason instanceof AsyncInteropPromise) {
            throw new \InvalidArgumentException('You cannot create React\Promise\RejectedPromise with a promise. Use React\Promise\reject($promiseOrValue) instead.');
        }

        $this->reason = $reason;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onRejected) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onRejected) {
            queue()->enqueue(function () use ($resolve, $reject, $onRejected) {
                try {
                    $resolve($onRejected($this->reason));
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
        queue()->enqueue(function () use ($onRejected) {
            if (null === $onRejected) {
                throw UnhandledRejectionException::resolve($this->reason);
            }

            $result = $onRejected($this->reason);

            if ($result instanceof self) {
                throw UnhandledRejectionException::resolve($result->reason);
            }

            if ($result instanceof ExtendedPromiseInterface) {
                $result->done();
            }
        });
    }

    public function otherwise(callable $onRejected)
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(null, function ($reason) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
                return new RejectedPromise($reason);
            });
        });
    }

    public function cancel()
    {
    }

    public function when(callable $onResolved)
    {
        try {
            $onResolved(
                UnhandledRejectionException::resolve($this->reason),
                null
            );
        } catch (\Throwable $exception) {
            AsyncInteropPromise\ErrorHandler::notify($exception);
        } catch (\Exception $exception) {
            AsyncInteropPromise\ErrorHandler::notify($exception);
        }
    }
}
