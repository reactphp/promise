<?php

namespace React\Promise\PromiseTest;

use Exception;
use LogicException;
use React\Promise;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;
use stdClass;
use function React\Promise\reject;
use function React\Promise\resolve;

trait ResolveTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function resolveShouldResolve(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(1);
    }

    /** @test */
    public function resolveShouldResolveWithPromisedValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(resolve(1));
    }

    /** @test */
    public function resolveShouldRejectWhenResolvedWithRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->resolve(reject($exception));
    }

    /** @test */
    public function resolveShouldForwardValueWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );

        $adapter->resolve(1);
    }

    /** @test */
    public function resolveShouldMakePromiseImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(function ($value) use ($adapter) {
                $adapter->resolve(3);

                return $value;
            })
            ->then(
                $mock,
                $this->expectCallableNever()
            );

        $adapter->resolve(1);
        $adapter->resolve(2);
    }

    /**
     * @test
     */
    public function resolveShouldRejectWhenResolvedWithItself(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(new LogicException('Cannot resolve a promise with itself.'));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->resolve($adapter->promise());
    }

    /**
     * @test
     */
    public function resolveShouldRejectWhenResolvedWithAPromiseWhichFollowsItself(): void
    {
        $adapter1 = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(new LogicException('Cannot resolve a promise with itself.'));

        $promise1 = $adapter1->promise();

        $promise2 = $adapter2->promise();

        $promise2->then(
            $this->expectCallableNever(),
            $mock
        );

        $adapter1->resolve($promise2);
        $adapter2->resolve($promise1);
    }

    /** @test */
    public function finallyShouldNotSuppressValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->finally(function () {})
            ->then($mock);

        $adapter->resolve($value);
    }

    /** @test */
    public function finallyShouldNotSuppressValueWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->finally(function (): int { // @phpstan-ignore-line
                return 1;
            })
            ->then($mock);

        $adapter->resolve($value);
    }

    /** @test */
    public function finallyShouldNotSuppressValueWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->finally(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then($mock);

        $adapter->resolve($value);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerThrowsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->resolve(1);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerRejectsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $mock);

        $adapter->resolve(1);
    }
}
