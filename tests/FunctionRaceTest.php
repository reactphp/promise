<?php

namespace React\Promise;

use Exception;

class FunctionRaceTest extends TestCase
{
    /** @test */
    public function shouldReturnForeverPendingPromiseForEmptyInput(): void
    {
        race(
            []
        )->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        race(
            [1, 2, 3]
        )->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($mock);

        $d2->resolve(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    /** @test */
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(null));

        race(
            [null, 1, null, 2, 3]
        )->then($mock);
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

        race($gen)->then($mock);
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

        race($gen)->then($mock);
    }

    /** @test */
    public function shouldRejectIfFirstSettledPromiseRejects(): void
    {
        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($this->expectCallableNever(), $mock);

        $d2->reject($exception);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    /** @test */
    public function shouldCancelInputArrayPromises(): void
    {
        $promise1 = new Promise(function () {}, $this->expectCallableOnce());
        $promise2 = new Promise(function () {}, $this->expectCallableOnce());

        race([$promise1, $promise2])->cancel();
    }

    /** @test */
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills(): void
    {
        $deferred = new Deferred($this->expectCallableNever());
        $deferred->resolve(null);

        $promise2 = new Promise(function () {}, $this->expectCallableNever());

        race([$deferred->promise(), $promise2])->cancel();
    }

    /** @test */
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseRejects(): void
    {
        $deferred = new Deferred($this->expectCallableNever());
        $deferred->reject(new Exception());

        $promise2 = new Promise(function () {}, $this->expectCallableNever());

        race([$deferred->promise(), $promise2])->cancel();
    }
}
