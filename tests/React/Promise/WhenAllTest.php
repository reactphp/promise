<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenAll
 */
class WhenAllTest extends TestCase
{
    /** @test */
    public function shouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array()));

        When::all(array(), $mock);
    }

    /** @test */
    public function shouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2, 3)));

        When::all(
            array(1, 2, 3),
            $mock
        );
    }

    /** @test */
    public function shouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2, 3)));

        When::all(
            array(new FulfilledPromise(1), new FulfilledPromise(2), new FulfilledPromise(3)),
            $mock
        );
    }

    /** @test */
    public function shouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(null, 1, null, 1, 1)));

        When::all(
            array(null, 1, null, 1, 1),
            $mock
        );
    }

    /** @test */
    public function shouldRejectIfAnyInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        When::all(
            array(new FulfilledPromise(1), new RejectedPromise(2), new FulfilledPromise(3)),
            $this->expectCallableNever(),
            $mock
        );
    }

    /** @test */
    public function shouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2, 3)));

        When::all(
            new FulfilledPromise(array(1, 2, 3)),
            $mock
        );
    }

    /** @test */
    public function shouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array()));

        When::all(
            new FulfilledPromise(1),
            $mock
        );
    }
}
