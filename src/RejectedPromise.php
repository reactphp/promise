<?php

namespace React\Promise;

final class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\RejectedPromise with a promise. Use React\Promise\reject($promiseOrValue) instead.');
        }

        $this->reason = $reason;
    }

    /**
     * @inheritdoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onRejected) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onRejected) {
            queue()->enqueue(function () use ($resolve, $reject, $onRejected) {
                try {
                    $resolve($onRejected($this->reason));
                } catch (\Throwable $exception) {
                    $reject($exception);
                } catch (\Exception $exception) {
                    $reject($exception);
                }
            });
        });
    }

    /**
     * @inheritdoc
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        queue()->enqueue(function () use ($onRejected) {
            if (null === $onRejected) {
                throw UnhandledRejectionException::resolve($this->reason);
            }

            $result = $onRejected($this->reason);

            if ($result instanceof self) {
                throw UnhandledRejectionException::resolve($result->reason);
            }

            if ($result instanceof PromiseInterface) {
                $result->done();
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function otherwise(callable $onRejected)
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    /**
     * @inheritdoc
     */
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(null, function ($reason) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
                return new RejectedPromise($reason);
            });
        });
    }

    /**
     * @inheritdoc
     */
    public function cancel()
    {
    }
}
