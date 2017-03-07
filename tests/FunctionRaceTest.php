<?php

namespace React\Promise;

class FunctionRaceTest extends TestCase
{
    /** @test */
    public function shouldReturnForeverPendingPromiseForEmptyInput()
    {
        race(
            []
        )->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        race(
            [1, 2, 3]
        )->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

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
    public function shouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            [null, 1, null, 2, 3]
        )->then($mock);
    }

    /** @test */
    public function shouldRejectIfFirstSettledPromiseRejects()
    {
        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

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
    public function shouldCancelInputArrayPromises()
    {
        $mock1 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock1
            ->expects($this->once())
            ->method('cancel');

        $mock2 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->once())
            ->method('cancel');

        race([$mock1, $mock2])->cancel();
    }

    /** @test */
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = New Deferred($mock);
        $deferred->resolve();

        $mock2 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        race([$deferred->promise(), $mock2])->cancel();
    }

    /** @test */
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = New Deferred($mock);
        $deferred->reject(new \Exception());

        $mock2 = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        race([$deferred->promise(), $mock2])->cancel();
    }
}
