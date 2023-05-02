<?php

namespace React\Promise;

/**
 * @deprecated 2.8.0 External usage of FulfilledPromise is deprecated, use `resolve()` instead.
 * @template-implements PromiseInterface
 * @template-covariant T
 */
class FulfilledPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    /**
     * @var T
     */
    private $value;

    /**
     * @param T $value
     */
    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    /**
     * @template TFulfilled as PromiseInterface<T>|T
     * @param (callable(T): TFulfilled)|null $onFulfilled
     * @return ($onFulfilled is null ? $this : (TFulfilled is PromiseInterface ? TFulfilled : PromiseInterface<TFulfilled>))
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }

        try {
            return resolve($onFulfilled($this->value));
        } catch (\Throwable $exception) {
            return new RejectedPromise($exception);
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return;
        }

        $result = $onFulfilled($this->value);

        if ($result instanceof ExtendedPromiseInterface) {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected)
    {
        return $this;
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        });
    }

    public function progress(callable $onProgress)
    {
        return $this;
    }

    public function cancel()
    {
    }
}
