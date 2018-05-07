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

        // Explicitly overwrite arguments with null values before invoking
        // resolver function. This ensure that these arguments do not show up
        // in the stack trace in PHP 7+ only.
        $cb = $resolver;
        $resolver = $canceller = null;
        $this->call($cb);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null !== $this->result) {
            return $this->result->then($onFulfilled, $onRejected, $onProgress);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected, $onProgress));
        }

        // keep a reference to this promise instance for the static canceller function.
        // see also parentCancellerFunction() for more details.
        $parent = $this;
        ++$parent->requiredCancelRequests;

        return new static(
            $this->resolver($onFulfilled, $onRejected, $onProgress),
            self::parentCancellerFunction($parent)
        );
    }

    /**
     * Creates a static parent canceller callback that is not bound to a promise instance.
     *
     * Moving the closure creation to a static method allows us to create a
     * callback that is not bound to a promise instance. By passing the target
     * promise instance by reference, we can still execute its cancellation logic
     * and still clear this reference after invocation (canceller can only ever
     * be called once). This helps avoiding garbage cycles if the parent canceller
     * creates an Exception.
     *
     * These assumptions are covered by the test suite, so if you ever feel like
     * refactoring this, go ahead, any alternative suggestions are welcome!
     *
     * @param Promise $parent
     * @return callable
     */
    private static function parentCancellerFunction(self &$parent)
    {
        return function () use (&$parent) {
            --$parent->requiredCancelRequests;

            if ($parent->requiredCancelRequests <= 0) {
                $parent->cancel();
            }

            $parent = null;
        };
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

    private function reject($reason = null)
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(reject($reason));
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
        } else {
            $this->canceller = null;
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

    private function call(callable $cb)
    {
        // Explicitly overwrite argument with null value. This ensure that this
        // argument does not show up in the stack trace in PHP 7+ only.
        $callback = $cb;
        $cb = null;

        // Use reflection to inspect number of arguments expected by this callback.
        // We did some careful benchmarking here: Using reflection to avoid unneeded
        // function arguments is actually faster than blindly passing them.
        // Also, this helps avoiding unnecessary function arguments in the call stack
        // if the callback creates an Exception (creating garbage cycles).
        if (is_array($callback)) {
            $ref = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (is_object($callback) && !$callback instanceof \Closure) {
            $ref = new \ReflectionMethod($callback, '__invoke');
        } else {
            $ref = new \ReflectionFunction($callback);
        }
        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                // keep a reference to this promise instance for the static resolve/reject functions.
                // see also resolveFunction() and rejectFunction() for more details.
                $target =& $this;

                $callback(
                    self::resolveFunction($target),
                    self::rejectFunction($target),
                    self::notifyFunction($this->progressHandlers)
                );
            }
        } catch (\Throwable $e) {
            $target = null;
            $this->reject($e);
        } catch (\Exception $e) {
            $target = null;
            $this->reject($e);
        }
    }

    /**
     * Creates a static resolver callback that is not bound to a promise instance.
     *
     * Moving the closure creation to a static method allows us to create a
     * callback that is not bound to a promise instance. By passing the target
     * promise instance by reference, we can still execute its resolving logic
     * and still clear this reference when settling the promise. This helps
     * avoiding garbage cycles if any callback creates an Exception.
     *
     * These assumptions are covered by the test suite, so if you ever feel like
     * refactoring this, go ahead, any alternative suggestions are welcome!
     *
     * @param Promise $target
     * @return callable
     */
    private static function resolveFunction(self &$target)
    {
        return function ($value = null) use (&$target) {
            if ($target !== null) {
                $target->settle(resolve($value));
                $target = null;
            }
        };
    }

    /**
     * Creates a static rejection callback that is not bound to a promise instance.
     *
     * Moving the closure creation to a static method allows us to create a
     * callback that is not bound to a promise instance. By passing the target
     * promise instance by reference, we can still execute its rejection logic
     * and still clear this reference when settling the promise. This helps
     * avoiding garbage cycles if any callback creates an Exception.
     *
     * These assumptions are covered by the test suite, so if you ever feel like
     * refactoring this, go ahead, any alternative suggestions are welcome!
     *
     * @param Promise $target
     * @return callable
     */
    private static function rejectFunction(self &$target)
    {
        return function ($reason = null) use (&$target) {
            if ($target !== null) {
                $target->reject($reason);
                $target = null;
            }
        };
    }

    /**
     * Creates a static progress callback that is not bound to a promise instance.
     *
     * Moving the closure creation to a static method allows us to create a
     * callback that is not bound to a promise instance. By passing its progress
     * handlers by reference, we can still execute them when requested and still
     * clear this reference when settling the promise. This helps avoiding
     * garbage cycles if any callback creates an Exception.
     *
     * These assumptions are covered by the test suite, so if you ever feel like
     * refactoring this, go ahead, any alternative suggestions are welcome!
     *
     * @param array $progressHandlers
     * @return callable
     */
    private static function notifyFunction(&$progressHandlers)
    {
        return function ($update = null) use (&$progressHandlers) {
            foreach ($progressHandlers as $handler) {
                $handler($update);
            }
        };
    }
}
