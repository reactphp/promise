<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenMap
 */
class WhenMapTest extends TestCase
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
            return new FulfilledPromise($val * 2);
        };
    }

    /** @test */
    public function shouldMapInputValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(2, 4, 6)));

        When::map(
            array(1, 2, 3),
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
            ->with($this->identicalTo(array(2, 4, 6)));

        When::map(
            array(new FulfilledPromise(1), new FulfilledPromise(2), new FulfilledPromise(3)),
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
            ->with($this->identicalTo(array(2, 4, 6)));

        When::map(
            array(1, new FulfilledPromise(2), 3),
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
            ->with($this->identicalTo(array(2, 4, 6)));

        When::map(
            array(1, 2, 3),
            $this->promiseMapper()
        )->then($mock);
    }

    /** @test */
    public function shouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(2, 4, 6)));

        When::map(
            new FulfilledPromise(array(1, new FulfilledPromise(2), 3)),
            $this->mapper()
        )->then($mock);
    }

    /** @test */
    public function shouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array()));

        When::map(
            new FulfilledPromise(1),
            $this->mapper()
        )->then($mock);
    }

    /** @test */
    public function shouldRejectWhenInputContainsRejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        When::map(
            array(new FulfilledPromise(1), new RejectedPromise(2), new FulfilledPromise(3)),
            $this->mapper()
        )->then($this->expectCallableNever(), $mock);
    }
}
