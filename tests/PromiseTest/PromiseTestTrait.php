<?php

namespace React\Promise\PromiseTest;

trait PromiseTestTrait
{
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function thenShouldReturnAPromise()
    {
        extract($this->getPromiseTestAdapter());

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $promise()->then());
    }

    /** @test */
    public function thenShouldReturnAllowNull()
    {
        extract($this->getPromiseTestAdapter());

        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $promise()->then(null, null, null));
    }
}
