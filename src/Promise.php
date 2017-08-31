<?php

namespace React\Promise;

class Promise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    private $canceller;
    private $result;

    private $handlers = [];
    private $progressHandlers = [];

    private $requiredCancelRequests = 0;

    public function __construct(callable $resolver, callable $canceller = null)
    {
        $this->canceller = $canceller;
        $this->call($resolver);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null !== $this->result) {
            return $this->result->then($onFulfilled, $onRejected, $onProgress);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected, $onProgress));
        }

        $this->requiredCancelRequests++;

        return new static($this->resolver($onFulfilled, $onRejected, $onProgress), function () {
            $this->requiredCancelRequests--;

            if ($this->requiredCancelRequests <= 0) {
                $this->cancel();
            }
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null !== $this->result) {
            return $this->result->done($onFulfilled, $onRejected, $onProgress);
        }

        $this->handlers[] = function (ExtendedPromiseInterface $promise) use ($onFulfilled, $onRejected) {
            $promise
                ->done($onFulfilled, $onRejected);
        };

        if ($onProgress) {
            $this->progressHandlers[] = $onProgress;
        }
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

    public function progress(callable $onProgress)
    {
        return $this->then(null, null, $onProgress);
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

        if (null !== $canceller) {
            $this->call($canceller);
        }

        // For BC, we call the parent canceller after our own canceller
        if ($parentCanceller) {
            $parentCanceller();
        }
    }

    private function resolver(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return function ($resolve, $reject, $notify) use ($onFulfilled, $onRejected, $onProgress) {
            if ($onProgress) {
                $progressHandler = function ($update) use ($notify, $onProgress) {
                    try {
                        $notify($onProgress($update));
                    } catch (\Throwable $e) {
                        $notify($e);
                    } catch (\Exception $e) {
                        $notify($e);
                    }
                };
            } else {
                $progressHandler = $notify;
            }

            $this->handlers[] = function (ExtendedPromiseInterface $promise) use ($onFulfilled, $onRejected, $resolve, $reject, $progressHandler) {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->done($resolve, $reject, $progressHandler);
            };

            $this->progressHandlers[] = $progressHandler;
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

    private function notify($update = null)
    {
        if (null !== $this->result) {
            return;
        }

        foreach ($this->progressHandlers as $handler) {
            $handler($update);
        }
    }

    private function settle(ExtendedPromiseInterface $promise)
    {
        $promise = $this->unwrap($promise);

        if ($promise === $this) {
            $promise = new RejectedPromise(
                new \LogicException('Cannot resolve a promise with itself.')
            );
        }

        if ($promise instanceof self) {
            $promise->requiredCancelRequests++;
        }

        $handlers = $this->handlers;

        $this->progressHandlers = $this->handlers = [];
        $this->result = $promise;

        foreach ($handlers as $handler) {
            $handler($promise);
        }
    }

    private function unwrap($promise)
    {
        $promise = $this->extract($promise);

        while ($promise instanceof self && null !== $promise->result) {
            $promise = $this->extract($promise->result);
        }

        return $promise;
    }

    private function extract($promise)
    {
        if ($promise instanceof LazyPromise) {
            $promise = $promise->promise();
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
                },
                function ($update = null) {
                    $this->notify($update);
                }
            );
        } catch (\Throwable $e) {
            $this->reject($e);
        } catch (\Exception $e) {
            $this->reject($e);
        }
    }
}
