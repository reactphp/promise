<?php

namespace React\Promise;

use AsyncInterop\Promise as AsyncInteropPromise;

final class LazyPromise implements PromiseInterface, AsyncInteropPromise
{
    private $factory;
    private $promise;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return $this->promise()->then($onFulfilled, $onRejected);
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        return $this->promise()->done($onFulfilled, $onRejected);
    }

    public function otherwise(callable $onRejected)
    {
        return $this->promise()->otherwise($onRejected);
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->promise()->always($onFulfilledOrRejected);
    }

    public function cancel()
    {
        return $this->promise()->cancel();
    }

    public function when(callable $onResolved)
    {
        return $this->promise()->when($onResolved);
    }

    /**
     * @internal
     * @see Promise::settle()
     */
    public function promise()
    {
        if (null === $this->promise) {
            $factory = $this->factory;
            $this->factory = null;

            try {
                $this->promise = resolve($factory());
            } catch (\Throwable $exception) {
                $this->promise = new RejectedPromise($exception);
            } catch (\Exception $exception) {
                $this->promise = new RejectedPromise($exception);
            }
        }

        return $this->promise;
    }
}
