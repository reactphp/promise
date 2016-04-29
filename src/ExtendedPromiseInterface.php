<?php

namespace React\Promise;

interface ExtendedPromiseInterface extends PromiseInterface
{
    /**
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * @return ExtendedPromiseInterface
     */
    public function otherwise(callable $onRejected);

    /**
     * @return ExtendedPromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);
}
