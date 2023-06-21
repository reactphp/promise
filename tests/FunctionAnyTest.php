<?php

namespace React\Promise;

use Exception;
use React\Promise\Exception\CompositeException;
use React\Promise\Exception\LengthException;

class FunctionAnyTest extends TestCase
{
    /** @test */
    public function shouldRejectWithLengthExceptionWithEmptyInputArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(
                self::callback(function ($exception) {
                    return $exception instanceof LengthException &&
                           'Must contain at least 1 item but contains only 0 items.' === $exception->getMessage();
                })
            );

        any([])
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldRejectWithLengthExceptionWithEmptyInputGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(new LengthException('Must contain at least 1 item but contains only 0 items.'));

        $gen = (function () {
            if (false) { // @phpstan-ignore-line
                yield;
            }
        })();

        any($gen)->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldResolveWithAnInputValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        any([1, 2, 3])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveWithAPromisedInputValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        any([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveWithAnInputValueFromDeferred(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        $deferred = new Deferred();

        any([$deferred->promise()])->then($mock);

        $deferred->resolve(1);
    }

    /** @test */
    public function shouldResolveValuesGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        $gen = (function () {
            for ($i = 1; $i <= 3; ++$i) {
                yield $i;
            }
        })();

        any($gen)->then($mock);
    }

    /** @test */
    public function shouldResolveValuesInfiniteGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        $gen = (function () {
            for ($i = 1; ; ++$i) {
                yield $i;
            }
        })();

        any($gen)->then($mock);
    }

    /** @test */
    public function shouldRejectWithAllRejectedInputValuesIfAllInputsAreRejected(): void
    {
        $exception1 = new Exception();
        $exception2 = new Exception();
        $exception3 = new Exception();

        $compositeException = new CompositeException(
            [0 => $exception1, 1 => $exception2, 2 => $exception3],
            'All promises rejected.'
        );

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with($compositeException);

        any([reject($exception1), reject($exception2), reject($exception3)])
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldRejectWithAllRejectedInputValuesIfInputIsRejectedFromDeferred(): void
    {
        $exception = new Exception();

        $compositeException = new CompositeException(
            [2 => $exception],
            'All promises rejected.'
        );

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with($compositeException);

        $deferred = new Deferred();

        any([2 => $deferred->promise()])->then($this->expectCallableNever(), $mock);

        $deferred->reject($exception);
    }

    /** @test */
    public function shouldResolveWhenFirstInputPromiseResolves(): void
    {
        $exception2 = new Exception();
        $exception3 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        any([resolve(1), reject($exception2), reject($exception3)])
            ->then($mock);
    }

    /** @test */
    public function shouldNotRelyOnArryIndexesWhenUnwrappingToASingleResolutionValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();

        any(['abc' => $d1->promise(), 1 => $d2->promise()])
            ->then($mock);

        $d2->resolve(2);
        $d1->resolve(1);
    }

    /** @test */
    public function shouldCancelInputArrayPromises(): void
    {
        $promise1 = new Promise(function () {}, $this->expectCallableOnce());
        $promise2 = new Promise(function () {}, $this->expectCallableOnce());

        any([$promise1, $promise2])->cancel();
    }

    /** @test */
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills(): void
    {
        $deferred = new Deferred($this->expectCallableNever());
        $deferred->resolve(null);

        $promise2 = new Promise(function () {}, $this->expectCallableNever());

        any([$deferred->promise(), $promise2])->cancel();
    }
}
