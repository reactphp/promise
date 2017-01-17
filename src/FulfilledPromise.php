<?php

namespace React\Promise;

final class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onFulfilled) {
            queue()->enqueue(function () use ($resolve, $reject, $onFulfilled) {
                try {
                    $resolve($onFulfilled($this->value));
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
        if (null === $onFulfilled) {
            return;
        }

        queue()->enqueue(function () use ($onFulfilled) {
            $result = $onFulfilled($this->value);

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
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
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
