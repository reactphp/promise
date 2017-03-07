<?php

namespace React\Promise;

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
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

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
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

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
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

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
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

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
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

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
        $exception2 = new \Exception();
        $exception3 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        map(
            [resolve(1), reject($exception2), resolve($exception3)],
            $this->mapper()
        )->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldCancelInputArrayPromises()
    {
        $mock1 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock1
            ->method('then')
            ->will($this->returnSelf());
        $mock1
            ->expects($this->once())
            ->method('cancel');

        $mock2 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock2
            ->method('then')
            ->will($this->returnSelf());
        $mock2
            ->expects($this->once())
            ->method('cancel');

        map(
            [$mock1, $mock2],
            $this->mapper()
        )->cancel();
    }
}
