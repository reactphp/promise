<?php

namespace React\Promise;

class Deferred implements PromiseInterface, ResolverInterface, PromisorInterface
{
    private $completed;
    private $promise;
    private $resolver;
    private $handlers = array();
    private $progressHandlers = array();

    public function then($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        if (null !== $this->completed) {
            return $this->completed->then($onFulfilled, $onRejected, $onProgress);
        }

        $deferred = new static();

        if (is_callable($onProgress)) {
            $progHandler = function ($update) use ($deferred, $onProgress) {
                try {
                    $deferred->progress(call_user_func($onProgress, $update));
                } catch (\Exception $e) {
                    $deferred->progress($e);
                }
            };
        } else {
            if (null !== $onProgress) {
                trigger_error('Invalid $onProgress argument passed to then(), must be null or callable.', E_USER_NOTICE);
            }

            $progHandler = array($deferred, 'progress');
        }

        $this->handlers[] = function ($promise) use ($onFulfilled, $onRejected, $deferred, $progHandler) {
            $promise
                ->then($onFulfilled, $onRejected)
                ->then(
                    array($deferred, 'resolve'),
                    array($deferred, 'reject'),
                    $progHandler
                );
        };

        $this->progressHandlers[] = $progHandler;

        return $deferred->promise();
    }

    public function resolve($value = null)
    {
        if (null !== $this->completed) {
            return Util::promiseFor($value);
        }

        $this->completed = Util::promiseFor($value);

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
