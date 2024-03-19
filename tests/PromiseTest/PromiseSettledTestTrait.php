<?php

namespace React\Promise\PromiseTest;

use React\Promise\Internal\RejectedPromise;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

trait PromiseSettledTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function thenShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());

        if ($adapter->promise() instanceof RejectedPromise) {
            $adapter->promise()->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    /** @test */
    public function thenShouldReturnAllowNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));

        if ($adapter->promise() instanceof RejectedPromise) {
            $adapter->promise()->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    /** @test */
    public function cancelShouldHaveNoEffectForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle(null);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function finallyShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $promise = $adapter->promise()->finally(function () {}));

        if ($promise instanceof RejectedPromise) {
            $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $promise = $adapter->promise()->always(function () {}));

        if ($promise instanceof RejectedPromise) {
            $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }
}
