<?php

namespace React\Promise;

use React\Promise\Exception\InvalidArgumentException;

final class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason)
    {
        if (!$reason instanceof \Throwable && !$reason instanceof \Exception) {
            throw InvalidArgumentException::invalidRejectionReason($reason);
        }

        $this->reason = $reason;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onRejected) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onRejected) {
            enqueue(function () use ($resolve, $reject, $onRejected) {
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
        enqueue(function () use ($onRejected) {
            if (null === $onRejected) {
                throw $this->reason;
            }

            $result = $onRejected($this->reason);

            if ($result instanceof self) {
                throw $result->reason;
            }

            if ($result instanceof PromiseInterface) {
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
}
