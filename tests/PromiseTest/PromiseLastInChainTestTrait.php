<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

use function React\Promise\reject;

trait PromiseLastInChainTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function notResolvedOrNotRejectedPromiseShouldNoThrow()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->then($this->expectCallableNever(), $this->expectCallableNever());

        self::assertTrue(true);
    }

    /** @test */
    public function unresolvedOrRejectedPromiseShouldNoThrow()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->then($this->expectCallableOnce(), $this->expectCallableNever());

        $adapter->resolve(true);

        self::assertTrue(true);
    }

    /** @test */
    public function throwWhenLastInChainWhenRejected()
    {
        $this->expectException(\Exception::class);

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new \Exception('Boom!'));
    }

    /** @test */
    public function doNotThrowWhenLastInChainWhenRejectedAndTheRejectionIsHandled()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->then($this->expectCallableNever(), $this->expectCallableOnce());

        $adapter->reject(new \Exception('Boom!'));
    }

    /** @test */
    public function throwWhenLastInChainWhenRejectedTransformedFromResolvedPromiseIntoRejected()
    {
        $this->expectException(\Exception::class);

        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->then(static function (string $message): PromiseInterface {
            return reject(new \Exception($message));
        }, $this->expectCallableNever());

        $adapter->resolve('Boom!');
    }

    /** @test */
    public function doNotThrowWhenLastInChainWhenRejectedAndTheRejectionIsHandledTransformedFromResolvedPromiseIntoRejected()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->then(static function (string $message): PromiseInterface {
            return reject(new \Exception($message));
        }, $this->expectCallableNever())->then($this->expectCallableNever(), $this->expectCallableOnce());

        $adapter->resolve('Boom!');
    }
}
