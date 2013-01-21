<?php

namespace React\Promise;

class Deferred implements PromiseInterface, ResolverInterface, PromisorInterface
{
    private $completed;
    private $promise;
    private $resolver;
    private $handlers = array();
    private $progressHandlers = array();

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        if (null !== $this->completed) {
            return $this->completed->then($fulfilledHandler, $errorHandler, $progressHandler);
        }

        $deferred = new static();

        if (is_callable($progressHandler)) {
            $progHandler = function ($update) use ($deferred, $progressHandler) {
                try {
                    $deferred->progress(call_user_func($progressHandler, $update));
                } catch (\Exception $e) {
                    $deferred->progress($e);
                }
            };
        } else {
            if (null !== $progressHandler) {
                trigger_error('Invalid $progressHandler argument passed to then(), must be null or callable.', E_USER_NOTICE);
            }

            $progHandler = array($deferred, 'progress');
        }

        $this->handlers[] = function ($promise) use ($fulfilledHandler, $errorHandler, $deferred, $progHandler) {
            $promise
                ->then($fulfilledHandler, $errorHandler)
                ->then(
                    array($deferred, 'resolve'),
                    array($deferred, 'reject'),
                    $progHandler
                );
        };

        $this->progressHandlers[] = $progHandler;

        return $deferred->promise();
    }

    public function resolve($result = null)
    {
        if (null !== $this->completed) {
            return Util::promiseFor($result);
        }

        $this->completed = Util::promiseFor($result);

        $this->processQueue($this->handlers, $this->completed);

        $this->progressHandlers = $this->handlers = array();

        return $this->completed;
    }

    public function reject($reason = null)
    {
        return $this->resolve(Util::rejectedPromiseFor($reason));
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
            call_user_func($handler, $value);
        }
    }
}
