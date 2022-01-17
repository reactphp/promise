<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

trait PromiseSettledTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function thenShouldReturnAPromiseForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    /** @test */
    public function cancelShouldReturnNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();

        self::assertNull($adapter->promise()->cancel());
    }

    /** @test */
    public function cancelShouldHaveNoEffectForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle();

        $adapter->promise()->cancel();
    }

    /** @test */
    public function doneShouldReturnNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        self::assertNull($adapter->promise()->done(null, function () {}));
    }

    /** @test */
    public function doneShouldReturnAllowNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        self::assertNull($adapter->promise()->done(null, function () {}, null));
    }

    /** @test */
    public function alwaysShouldReturnAPromiseForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(function () {}));
    }
}
