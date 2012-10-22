<?php

namespace Promise;

/**
 * A Deferred represents a computation or unit of work that may not have
 * completed yet. Typically (but not always), that computation will be something
 * that executes asynchronously and completes at some point in the future.
 */
class Deferred implements PromiseInterface, ResolverInterface
{
    /**
     * @var PromiseInterface
     */
    private $completed;

    /**
     * @var DeferredPromise
     */
    private $promise;

    /**
     * @var DeferredResolver
     */
    private $resolver;

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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function reject($error = null)
    {
        return $this->resolve(new RejectedPromise($error));
    }

    /**
     * {@inheritDoc}
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
     * @return DeferredPromise
     */
    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new DeferredPromise($this);
        }

        return $this->promise;
    }

    /**
     * Return a Resolver for this Deferred.
     *
     * This can be given safely to components to produce a result but not to
     * know any details about consumers.
     *
     * @return DeferredResolver
     */
    public function resolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new DeferredResolver($this);
        }

        return $this->resolver;
    }
}
