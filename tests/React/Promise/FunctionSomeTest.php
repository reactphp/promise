<?php

namespace React\Promise;

class FunctionSomeTest extends TestCase
{
    /** @test */
    public function shouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array()));

        some(
            array(),
            1
        )->then($mock);
    }

    /** @test */
    public function shouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2)));

        some(
            array(1, 2, 3),
            2
        )->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2)));

        some(
            array(resolve(1), resolve(2), resolve(3)),
            2
        )->then($mock);
    }

    /** @test */
    public function shouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(null, 1)));

        some(
            array(null, 1, null, 2, 3),
            2
        )->then($mock);
    }

    /** @test */
    public function shouldRejectIfAnyInputPromiseRejectsBeforeDesiredNumberOfInputsAreResolved()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1 => 2, 2 => 3)));

        some(
            array(resolve(1), reject(2), reject(3)),
            2
        )->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(1, 2)));

        some(
            resolve(array(1, 2, 3)),
            2
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

        some(
            resolve(1),
            1
        )->then($mock);
    }
}
