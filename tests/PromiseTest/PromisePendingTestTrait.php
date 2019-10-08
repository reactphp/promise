<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

trait PromisePendingTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function thenShouldReturnAPromiseForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    /** @test */
    public function cancelShouldReturnNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->cancel());
    }

    /** @test */
    public function doneShouldReturnNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done());
    }

    /** @test */
    public function doneShouldReturnAllowNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done(null, null));
    }

    /** @test */
    public function otherwiseShouldNotInvokeRejectionHandlerForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /** @test */
    public function alwaysShouldReturnAPromiseForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(function () {}));
    }
}
