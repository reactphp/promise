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

    private $requiredCancelRequests = 0;
    private $isCancelled = false;

    public function __construct(callable $resolver, callable $canceller = null)
    {
        $this->canceller = $canceller;
        $this->call($resolver);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null !== $this->result) {
            return $this->result->then($onFulfilled, $onRejected);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected));
        }

        $this->requiredCancelRequests++;

        return new static($this->resolver($onFulfilled, $onRejected), function () {
            $this->requiredCancelRequests--;

            if ($this->requiredCancelRequests <= 0) {
                $this->cancel();
            }
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null !== $this->result) {
            return $this->result->done($onFulfilled, $onRejected);
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
        $canceller = $this->canceller;
        $this->canceller = null;

        $parentCanceller = null;

        if (null !== $this->result) {
            // Go up the promise chain and reach the top most promise which is
            // itself not following another promise
            $root = $this->unwrap($this->result);

            // Return if the root promise is already resolved or a
            // FulfilledPromise or RejectedPromise
            if (!$root instanceof self || null !== $root->result) {
                return;
            }

            $root->requiredCancelRequests--;

            if ($root->requiredCancelRequests <= 0) {
                $parentCanceller = [$root, 'cancel'];
            }
        }

        $this->isCancelled = true;

        if (null !== $canceller) {
            $this->call($canceller);
        }

        // For BC, we call the parent canceller after our own canceller
        if ($parentCanceller) {
            $parentCanceller();
        }
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
        $result = $this->unwrap($result);

        if ($result === $this) {
            $result = new RejectedPromise(
                LogicException::circularResolution()
            );
        }

        if ($result instanceof self) {
            $result->requiredCancelRequests++;
        } else {
            // Unset canceller only when not following a pending promise
            $this->canceller = null;
        }

        $handlers = $this->handlers;

        $this->handlers = [];
        $this->result = $result;

        foreach ($handlers as $handler) {
            $handler($result);
        }
    }

    private function unwrap($promise)
    {
        while ($promise instanceof self && null !== $promise->result) {
            $promise = $promise->result;
        }

        return $promise;
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
