<?php

namespace React\Promise;

interface PromiseInterface
{
    /**
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return PromiseInterface
     */
    public function otherwise(callable $onRejected);

    /**
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * @return void
     */
    public function cancel();

    /**
     * @return bool
     */
    public function isFulfilled();

    /**
     * @return bool
     */
    public function isRejected();

    /**
     * @return bool
     */
    public function isPending();

    /**
     * @return bool
     */
    public function isCancelled();

    /**
     * @return mixed
     */
    public function value();

    /**
     * @return mixed
     */
    public function reason();
}
