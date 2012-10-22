<?php

namespace Promise\Tests;

use Promise\RejectedPromise;
use Promise\ResolvedPromise;
use Promise\When;

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
            array(new ResolvedPromise(1), new ResolvedPromise(2), new ResolvedPromise(3)),
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
            array(new ResolvedPromise(1), new RejectedPromise(2), new ResolvedPromise(3)),
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
            new ResolvedPromise(array(1, 2, 3)),
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
            new ResolvedPromise(1),
            $mock
        );
    }
}
