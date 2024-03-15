<?php

namespace React\Promise\PromiseTest;

use Exception;
use React\Promise;
use React\Promise\Deferred;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use function React\Promise\resolve;

trait RejectTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function rejectShouldRejectWithAnException(): void
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

        $adapter->reject($exception);
    }

    /** @test */
    public function rejectShouldForwardReasonWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject($exception);
    }

    /** @test */
    public function rejectShouldMakePromiseImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new Exception();
        $exception2 = new Exception();
        $exception3 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->promise()
            ->then(null, function (\Throwable $value) use ($exception3, $adapter): PromiseInterface {
                $adapter->reject($exception3);

                return reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject($exception1);
        $adapter->reject($exception2);
    }

    /** @test */
    public function rejectShouldInvokeCatchHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->catch($mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function finallyShouldNotSuppressRejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(function () {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(function (): int { // @phpstan-ignore-line
                return 1;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerThrowsForRejection(): void
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

        $adapter->reject($exception);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerRejectsForRejection(): void
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

        $adapter->reject($exception);
    }
}
