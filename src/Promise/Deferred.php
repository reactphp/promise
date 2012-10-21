<?php

namespace Promise;

class Deferred implements PromiseInterface
{
    /**
     * @var Promise
     */
    private $completed;

    /**
     * @var Promise
     */
    private $promise;

    /**
     * @var array
     */
    private $handlers = array();

    /**
     * @var array
     */
    private $progressHandlers = array();

    /**
     * {@inheritDoc}
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        if (null !== $this->completed) {
            return $this->completed->then($fulfilledHandler, $errorHandler, $progressHandler);
        }

        $deferred = new self();

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
                    array($deferred, 'resolve'),
                    array($deferred, 'reject'),
                    $progHandler
                );
        };

        $this->progressHandlers[] = $progHandler;

        return $deferred->promise();
    }

    /**
     * @param  mixed   $val
     * @return Promise
     */
    public function resolve($val = null)
    {
        if (null !== $this->completed) {
            return Util::resolve($val);
        }

        $this->completed = Util::resolve($val);

        foreach ($this->handlers as $handler) {
            call_user_func($handler, $this->completed);
        }

        $this->progressHandlers = $this->handlers = array();

        return $this->completed;
    }

    /**
     * @param  mixed   $err
     * @return Promise
     */
    public function reject($err = null)
    {
        return $this->resolve(new RejectedPromise($err));
    }

    /**
     * @param mixed $update
     */
    public function progress($update = null)
    {
        if (null !== $this->completed) {
            return;
        }

        foreach ($this->progressHandlers as $handler) {
            call_user_func($handler, $update);
        }
    }

    /**
     * @return Promise
     */
    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new DeferredPromise($this);
        }

        return $this->promise;
    }
}
