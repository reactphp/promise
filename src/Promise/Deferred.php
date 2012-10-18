<?php

namespace Promise;

use SplQueue;

class Deferred implements PromiseInterface
{
    const STATE_UNRESOLVED = 'unresolved';
    const STATE_RESOLVED   = 'resolved';
    const STATE_REJECTED   = 'rejected';

    /**
     * @var Promise
     */
    private $promise;

    /**
     * @var string
     */
    private $state = self::STATE_UNRESOLVED;

    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * @var array
     */
    private $arguments = array();

    public function __construct()
    {
        $this->queue = new SplQueue();
        $this->queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    /**
     * {@inheritDoc}
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        $deferred = new static();

        if ($fulfilledHandler) {
            $fulfilledHandler = function () use ($deferred, $fulfilledHandler) {
                try {
                    $ret = call_user_func_array($fulfilledHandler, func_get_args());
                } catch (\Exception $e) {
                    $deferred->reject($e);
                    throw $e;
                }

                $deferred->resolve($ret);

                return $ret;
            };
        }

        if ($errorHandler) {
            $errorHandler = function () use ($deferred, $errorHandler) {
                $ret = call_user_func_array($errorHandler, func_get_args());
                $deferred->reject($ret);

                return $ret;
            };
        }

        $this->queue->enqueue(array($fulfilledHandler, $errorHandler, $progressHandler));

        if ($this->state !== self::STATE_UNRESOLVED) {
            $this->fire();
        }

        return $deferred->promise();
    }

    /**
     * {@inheritDoc}
     */
    public function isResolved()
    {
        return $this->state === self::STATE_RESOLVED;
    }

    /**
     * {@inheritDoc}
     */
    public function isRejected()
    {
        return $this->state === self::STATE_REJECTED;
    }

    /**
     * Returns a Promise object which provides a subset of the methods of the
     * Deferred object (then, isResolved, and isRejected) to prevent users from
     * changing the state of the Deferred.
     *
     * @return Promise
     */
    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new Promise($this);
        }

        return $this->promise;
    }

    public function resolve()
    {
        $this->state = self::STATE_RESOLVED;
        $this->fire(func_get_args());

        return $this;
    }

    public function reject()
    {
        $this->state = self::STATE_REJECTED;
        $this->fire(func_get_args());

        return $this;
    }

    private function fire()
    {
        if (0 !== func_num_args()) {
            $this->arguments = func_get_arg(0);
        }

        foreach ($this->queue as $entry) {
            $fn = $this->state === self::STATE_REJECTED ? $entry[1] : $entry[0];

            if (!$fn) {
                continue;
            }

            try {
                call_user_func_array($fn, $this->arguments);
            } catch (\Exception $e) {
                $this->state = self::STATE_REJECTED;
                $this->arguments = array($e);
            }
        }
    }
}
