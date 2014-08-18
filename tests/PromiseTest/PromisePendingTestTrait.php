<?php

namespace React\Promise\PromiseTest;

trait PromisePendingTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function thenShouldReturnAPromiseForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $adapter->promise()->then(null, null, null));
    }
}
