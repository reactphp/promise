<?php

namespace React\Promise\PromiseTest;

use React\Promise\Internal\RejectedPromiseTest;
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

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    /** @test */
    public function cancelShouldHaveNoEffectForSettledPromise()
    {
        if ($this instanceof RejectedPromiseTest) {
            $this->markTestSkipped('Test skipped because the cancel function on a rejected promise is a dud');
        }

        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle(null);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function finallyShouldReturnAPromiseForSettledPromise()
    {
        try {
            $adapter = $this->getPromiseTestAdapter();

            $adapter->settle(null);
            self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(function () {}));
        } catch (\Exception $exception) {}
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldReturnAPromiseForSettledPromise()
    {
        try {
            $adapter = $this->getPromiseTestAdapter();

            $adapter->settle(null);
            self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(function () {
            }));
        } catch (\Exception $exception) {}
    }
}
