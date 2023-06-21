<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

trait PromiseSettledTestTrait
{
    abstract public function getPromiseTestAdapter(callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function thenShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
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
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(function () {}));
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(function () {}));
    }
}
