<?php

namespace React\Promise;

use Exception;

class FunctionMapTest extends TestCase
{
    protected function mapper()
    {
        return function ($val) {
            return $val * 2;
        };
    }

    protected function promiseMapper()
    {
        return function ($val) {
            return resolve($val * 2);
        };
    }

    /** @test */
    public function shouldMapInputValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->mapper()
        )->then($mock);
    }

    /** @test */
    public function shouldMapInputPromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([2, 4, 6]));

        map(
            [resolve(1), resolve(2), resolve(3)],
            $this->mapper()
        )->then($mock);
    }

    /** @test */
    public function shouldMapMixedInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([2, 4, 6]));

        map(
            [1, resolve(2), 3],
            $this->mapper()
        )->then($mock);
    }

    /** @test */
    public function shouldMapInputWhenMapperReturnsAPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->promiseMapper()
        )->then($mock);
    }

    /** @test */
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([2, 4, 6]));

        $deferred = new Deferred();

        map(
            [resolve(1), $deferred->promise(), resolve(3)],
            $this->mapper()
        )->then($mock);

        $deferred->resolve(2);
    }

    /** @test */
    public function shouldRejectWhenInputContainsRejection()
    {
        $exception2 = new Exception();
        $exception3 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception2));

        map(
            [resolve(1), reject($exception2), resolve($exception3)],
            $this->mapper()
        )->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldCancelInputArrayPromises()
    {
        $promise1 = new Promise(function () {}, $this->expectCallableOnce());
        $promise2 = new Promise(function () {}, $this->expectCallableOnce());

        map(
            [$promise1, $promise2],
            $this->mapper()
        )->cancel();
    }
}
