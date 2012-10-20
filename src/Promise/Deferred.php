<?php

namespace Promise;

class Deferred implements PromiseInterface
{
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

    public function __construct()
    {
        $this->thenCallback     = array($this, 'replaceableThen');
        $this->resolveCallback  = array($this, 'replaceableResolve');
        $this->progressCallback = array($this, 'replaceableProgress');
    }

    /**
     * {@inheritDoc}
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return call_user_func($this->thenCallback, $fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function resolve($val = null)
    {
        return call_user_func($this->resolveCallback, $val);
    }

    public function reject($err = null)
    {
        return call_user_func($this->resolveCallback, new RejectedPromise($err));
    }

    public function progress($update = null)
    {
        return call_user_func($this->progressCallback, $update);
    }

    /**
     * @return Promise
     */
    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new Promise(array($this, 'then'));
        }

        return $this->promise;
    }

    private function replaceableThen($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
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

    private function replaceableProgress($update = null)
    {
        foreach ($this->progressHandlers as $handler) {
            call_user_func($handler, $update);
        }
    }

    private function replaceableResolve($completed = null)
    {
        $completed = Util::resolve($completed);

        $this->thenCallback     = array($completed, 'then');
        $this->resolveCallback  = array('Promise\Util', 'resolve');
        $this->progressCallback = function () {};

        foreach ($this->handlers as $handler) {
            call_user_func($handler, $completed);
        }

        $this->progressHandlers = $this->handlers = array();

        return $completed;
    }
}
