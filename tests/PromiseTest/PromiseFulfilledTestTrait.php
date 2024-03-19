<?php

namespace React\Promise\PromiseTest;

use Exception;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;
use stdClass;
use function React\Promise\reject;
use function React\Promise\resolve;

trait PromiseFulfilledTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function fulfilledPromiseShouldBeImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->resolve(2);

        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function fulfilledPromiseShouldInvokeNewlyAddedCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());
    }

    /** @test */
    public function thenShouldForwardResultWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldForwardCallbackResultToNextCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return $val + 1;
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldForwardPromisedCallbackResultValueToNextCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return resolve($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function () use ($exception) {
                    return reject($exception);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackThrows(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function thenShouldContinueToExecuteCallbacksWhenPriorCallbackSuspendsFiber(): void
    {
        /** @var PromiseAdapterInterface<int> $adapter */
        $adapter = $this->getPromiseTestAdapter();
        $adapter->resolve(42);

        $fiber = new \Fiber(function () use ($adapter) {
            $adapter->promise()->then(function (int $value) {
                \Fiber::suspend($value);
            });
        });

        $ret = $fiber->start();
        $this->assertEquals(42, $ret);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(42));

        $adapter->promise()->then($mock);
    }

    /** @test */
    public function cancelShouldHaveNoEffectForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->resolve(null);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function catchShouldNotInvokeRejectionHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->catch($this->expectCallableNever());
    }

    /** @test */
    public function finallyShouldNotSuppressValueForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->finally(function () {})
            ->then($mock);
    }

    /** @test */
    public function finallyShouldNotSuppressValueWhenHandlerReturnsANonPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->finally(function (): int { // @phpstan-ignore-line
                return 1;
            })
            ->then($mock);
    }

    /** @test */
    public function finallyShouldNotSuppressValueWhenHandlerReturnsAPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->finally(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then($mock);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerThrowsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->finally(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerRejectsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->finally(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldNotInvokeRejectionHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressValueForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function () {})
            ->then($mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsANonPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function (): int { // @phpstan-ignore-line
                return 1;
            })
            ->then($mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsAPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then($mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerThrowsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerRejectsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $mock);
    }
}
