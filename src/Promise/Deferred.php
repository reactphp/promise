<?php

namespace Promise;

/**
 * A Deferred represents a computation or unit of work that may not have
 * completed yet. Typically (but not always), that computation will be something
 * that executes asynchronously and completes at some point in the future.
 */
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
     * Resolve this Deferred.
     *
     * All consumers are notified by having their $fulfilledHandler (which they
     * registered via then()) called with the result.
     *
     * @param  mixed   $result
     * @return Promise
     */
    public function resolve($result = null)
    {
        if (null !== $this->completed) {
            return Util::resolve($result);
        }

        $this->completed = Util::resolve($result);

        foreach ($this->handlers as $handler) {
            call_user_func($handler, $this->completed);
        }

        $this->progressHandlers = $this->handlers = array();

        return $this->completed;
    }

    /**
     * Reject this Deferred, signalling that the Deferred's computation failed.
     *
     * All consumers are notified by having their $errorHandler (which they
     * registered via then()) called with the error.
     *
     * @param  mixed   $error
     * @return Promise
     */
    public function reject($error = null)
    {
        return $this->resolve(new RejectedPromise($error));
    }

    /**
     * Trigger progress notifications, to indicate to consumers that the
     * computation is making progress toward its result.
     *
     * All consumers are notified by having their $progressHandler (which they
     * registered via then()) called with the update.
     *
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
     * Return a Promise for this Deferred.
     *
     * This can be given safely to consumers so that they can't modify the
     * Deferred (such as calling resolve or reject on it).
     *
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
