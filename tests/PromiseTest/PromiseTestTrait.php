<?php

namespace React\Promise\PromiseTest;

trait PromiseTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function thenShouldReturnAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $adapter->promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $adapter->promise()->then(null, null, null));
    }
}
