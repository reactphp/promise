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
            array(When::resolve(1), When::resolve(2), When::resolve(3)),
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
            array(When::resolve(1), When::reject(2), When::reject(3)),
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
            When::resolve(array(1, 2, 3)),
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
            When::resolve(1),
            1,
            $mock
        );
    }
}
