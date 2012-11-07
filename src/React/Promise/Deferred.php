<?php

namespace React\Promise;

class Deferred implements PromiseInterface, ResolverInterface
{
    private $completed;
    private $promise;
    private $resolver;
    private $handlers = array();
    private $progressHandlers = array();

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $deferred = new static();

        if (null !== $this->completed) {
            $completed = $this->completed;

            $this->executeCallback(function () use ($completed, $fulfilledHandler, $errorHandler, $deferred) {
                $completed
                    ->then($fulfilledHandler, $errorHandler)
                    ->then(
                        function ($value) use ($deferred) {
                            $deferred->resolve($value);
                        },
                        function ($reason) use ($deferred) {
                            $deferred->reject($reason);
                        },
                        function ($update) use ($deferred) {
                            $deferred->progress($update);
                        }
                    );
            });
        } else {
            if ($progressHandler) {
                $progHandler = function ($update) use ($deferred, $progressHandler) {
                    try {
                        $deferred->progress(call_user_func($progressHandler, $update));
                    } catch (\Exception $e) {
                        $deferred->progress($e);
                    }
                };
            } else {
                $progHandler = array($deferred, 'progress');
            }

            $this->handlers[] = function ($promise) use ($fulfilledHandler, $errorHandler, $deferred, $progHandler) {
                $promise
                    ->then($fulfilledHandler, $errorHandler)
                    ->then(
                        function ($value) use ($deferred) {
                            $deferred->resolve($value);
                        },
                        function ($reason) use ($deferred) {
                            $deferred->reject($reason);
                        },
                        $progHandler
                    );
            };

            $this->progressHandlers[] = $progHandler;
        }

        return $deferred->promise();
    }

    public function resolve($result = null)
    {
        if (null !== $this->completed) {
            $deferred = new static();
            $deferred->resolve($result);

            return $deferred->promise();
        }

        $this->completed = Util::promiseFor($result);

        $this->processQueue($this->handlers, $this->completed);

        $this->progressHandlers = $this->handlers = array();

        return $this->promise();
    }

    public function reject($reason = null)
    {
        return $this->resolve(new RejectedPromise($reason));
    }

    public function progress($update = null)
    {
        if (null !== $this->completed) {
            return;
        }

        $this->processQueue($this->progressHandlers, $update);
    }

    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new DeferredPromise($this);
        }

        return $this->promise;
    }

    public function resolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new DeferredResolver($this);
        }

        return $this->resolver;
    }

    protected function processQueue($queue, $value)
    {
        foreach ($queue as $handler) {
            $this->executeCallback($handler, array($value));
        }
    }

    protected function executeCallback($callback, array $args = array())
    {
        call_user_func_array($callback, $args);
    }
}
