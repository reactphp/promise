<?php

namespace React\Promise;

class Deferred implements PromiseInterface, ResolverInterface, PromisorInterface, CancellablePromiseInterface
{
    private $completed;
    private $promise;
    private $resolver;
    private $handlers = array();
    private $progressHandlers = array();
    private $canceller;

    private $requiredCancelRequests = 0;
    private $cancelRequests = 0;

    public function __construct($canceller = null)
    {
        if ($canceller !== null && !is_callable($canceller)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The canceller argument must be null or of type callable, %s given.',
                    gettype($canceller)
                )
            );
        }

        $this->canceller = $canceller;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        if (null !== $this->completed) {
            return $this->completed->then($fulfilledHandler, $errorHandler, $progressHandler);
        }

        $canceller = null;
        if ($this->canceller !== null) {
            $this->requiredCancelRequests++;

            $that = $this;
            $current =& $this->cancelRequests;
            $required =& $this->requiredCancelRequests;

            $canceller = function () use ($that, &$current, &$required) {
                if (++$current < $required) {
                    return;
                }

                $that->cancel();
            };
        }

        $deferred = new static($canceller);

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
            return resolve($result);
        }

        $this->completed = resolve($result);

        $this->processQueue($this->handlers, $this->completed);

        $this->progressHandlers = $this->handlers = array();

        return $this->completed;
    }

    public function reject($reason = null)
    {
        return $this->resolve(reject($reason));
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

    public function cancel()
    {
        if (null === $this->canceller || null !== $this->completed) {
            return;
        }

        $canceller = $this->canceller;
        $this->canceller = null;

        try {
            $that = $this;

            call_user_func(
                $canceller,
                function ($value = null) use ($that) {
                    $that->resolve($value);
                },
                function ($reason = null) use ($that) {
                    $that->reject($reason);
                },
                function ($update = null) use ($that) {
                    $that->progress($update);
                }
            );
        } catch (\Exception $e) {
            $this->reject($e);
        }
    }

    protected function processQueue($queue, $value)
    {
        foreach ($queue as $handler) {
            call_user_func($handler, $value);
        }
    }
}
