<?php

namespace React\Promise;

use React\Promise\Exception\LogicException;

final class Promise implements PromiseInterface
{
    private $canceller;

    /**
     * @var PromiseInterface
     */
    private $result;

    private $handlers = [];

    private $remainingCancelRequests = 0;
    private $isCancelled = false;

    public function __construct(callable $resolver, callable $canceller = null)
    {
        $this->canceller = $canceller;
        $this->call($resolver);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null !== $this->result) {
            return $this->result()->then($onFulfilled, $onRejected);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected));
        }

        $this->remainingCancelRequests++;

        return new static($this->resolver($onFulfilled, $onRejected), function () {
            if (--$this->remainingCancelRequests > 0) {
                return;
            }

            $this->cancel();
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null !== $this->result) {
            return $this->result()->done($onFulfilled, $onRejected);
        }

        $this->handlers[] = function (PromiseInterface $promise) use ($onFulfilled, $onRejected) {
            $promise
                ->done($onFulfilled, $onRejected);
        };
    }

    public function otherwise(callable $onRejected)
    {
        return $this->then(null, function ($reason) use ($onRejected) {
            if (!_checkTypehint($onRejected, $reason)) {
                return new RejectedPromise($reason);
            }

            return $onRejected($reason);
        });
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        }, function ($reason) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
                return new RejectedPromise($reason);
            });
        });
    }

    public function cancel()
    {
        if (null !== $this->result) {
            return;
        }

        $this->isCancelled = true;

        if (null === $this->canceller) {
            return;
        }

        $canceller = $this->canceller;
        $this->canceller = null;

        $this->call($canceller);
    }

    public function isFulfilled()
    {
        if (null !== $this->result) {
            return $this->result->isFulfilled();
        }

        return false;
    }

    public function isRejected()
    {
        if (null !== $this->result) {
            return $this->result->isRejected();
        }

        return false;
    }

    public function isPending()
    {
        if (null !== $this->result) {
            return $this->result->isPending();
        }

        return true;
    }

    public function isCancelled()
    {
        return $this->isCancelled;
    }

    public function value()
    {
        if (null !== $this->result) {
            return $this->result->value();
        }

        throw LogicException::valueFromNonFulfilledPromise();
    }

    public function reason()
    {
        if (null !== $this->result) {
            return $this->result->reason();
        }

        throw LogicException::reasonFromNonRejectedPromise();
    }

    private function resolver(callable $onFulfilled = null, callable $onRejected = null)
    {
        return function ($resolve, $reject) use ($onFulfilled, $onRejected) {
            $this->handlers[] = function (PromiseInterface $promise) use ($onFulfilled, $onRejected, $resolve, $reject) {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->done($resolve, $reject);
            };
        };
    }

    private function resolve($value = null)
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(resolve($value));
    }

    private function reject($reason = null)
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(reject($reason));
    }

    private function settle(PromiseInterface $result)
    {
        if ($result instanceof LazyPromise) {
            $result = $result->promise();
        }

        if ($result === $this) {
            $result = new RejectedPromise(
                LogicException::circularResolution()
            );
        }

        $handlers = $this->handlers;

        $this->handlers = [];
        $this->canceller = null;
        $this->result = $result;

        foreach ($handlers as $handler) {
            $handler($result);
        }
    }

    private function result()
    {
        while ($this->result instanceof self && null !== $this->result->result) {
            $this->result = $this->result->result;
        }

        return $this->result;
    }

    private function call(callable $callback)
    {
        try {
            $callback(
                function ($value = null) {
                    $this->resolve($value);
                },
                function ($reason = null) {
                    $this->reject($reason);
                }
            );
        } catch (\Throwable $e) {
            $this->reject($e);
        } catch (\Exception $e) {
            $this->reject($e);
        }
    }
}
