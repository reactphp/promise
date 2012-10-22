<?php

namespace Promise\Tests;

use Promise\RejectedPromise;
use Promise\ResolvedPromise;
use Promise\When;

/**
 * @group When
 * @group WhenAny
 */
class WhenAnyTest extends TestCase
{
    /** @test */
    public function shouldResolveToNullWithEmptyInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        When::any(array(), $mock);
    }

    /** @test */
    public function shouldResolveWithAnInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(1, 2, 3),
            $mock
        );
    }

    /** @test */
    public function shouldResolveWithAPromisedInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(new ResolvedPromise(1), new ResolvedPromise(2), new ResolvedPromise(3)),
            $mock
        );
    }

    /** @test */
    public function shouldRejectWithARejectedInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::all(
            array(new RejectedPromise(1), new ResolvedPromise(2), new ResolvedPromise(3)),
            $this->expectCallableNever(),
            $mock
        );
    }

    /** @test */
    public function shouldResolveWhenFirstInputPromiseResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(new ResolvedPromise(1), new RejectedPromise(2), new RejectedPromise(3)),
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
            ->with($this->identicalTo(1));

        When::any(
            new ResolvedPromise(array(1, 2, 3)),
            $mock
        );
    }

    /** @test */
    public function shouldResolveToNullArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        When::any(
            new ResolvedPromise(1),
            $mock
        );
    }
}
