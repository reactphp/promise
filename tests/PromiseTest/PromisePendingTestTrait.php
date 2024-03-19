<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

trait PromisePendingTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function thenShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    /** @test */
    public function catchShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->catch($this->expectCallableNever());
    }

    /** @test */
    public function finallyShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(function () {}));
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(function () {}));
    }
}
