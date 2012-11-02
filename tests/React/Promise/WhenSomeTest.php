<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenSome
 */
class WhenSomeTest extends TestCase
{
    /** @test */
    public function shouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array()));

        When::some(array(), 1, $mock);
    }

    /** @test */
    public function shouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2)));

        When::some(
            array(1, 2, 3),
            2,
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
            ->with($this->identicalTo(array(1, 2)));

        When::some(
            array(new FulfilledPromise(1), new FulfilledPromise(2), new FulfilledPromise(3)),
            2,
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
            ->with($this->identicalTo(array(null, 1)));

        When::some(
            array(null, 1, null, 2, 3),
            2,
            $mock
        );
    }

    /** @test */
    public function shouldRejectIfAnyInputPromiseRejectsBeforeDesiredNumberOfInputsAreResolved()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1 => 2, 2 => 3)));

        When::some(
            array(new FulfilledPromise(1), new RejectedPromise(2), new RejectedPromise(3)),
            2,
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
            ->with($this->identicalTo(array(1, 2)));

        When::some(
            new FulfilledPromise(array(1, 2, 3)),
            2,
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

        When::some(
            new FulfilledPromise(1),
            1,
            $mock
        );
    }
}
