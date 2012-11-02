<?php

namespace React\Promise;

/**
 * @group Deferred
 * @group DeferredResolve
 */
class DeferredResolveTest extends TestCase
{
    /** @test */
    public function shouldResolve()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->promise()
            ->then($mock);

        $d
            ->resolver()
            ->resolve(1);
    }

    /** @test */
    public function shouldResolveWithPromisedValue()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->promise()
            ->then($mock);

        $d
            ->resolver()
            ->resolve(new FulfilledPromise(1));
    }

    /** @test */
    public function shouldRejectWhenResolvedWithRejectedPromise()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->promise()
            ->then($this->expectCallableNever(), $mock);

        $d
            ->resolver()
            ->resolve(new RejectedPromise(1));
    }

    /** @test */
    public function shouldReturnAPromiseForTheResolutionValue()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->resolver()
            ->resolve(1)
            ->then($mock);
    }

    /** @test */
    public function shouldReturnAPromiseForAPromisedResolutionValue()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->resolver()
            ->resolve(When::resolve(1))
            ->then($mock);
    }

    /** @test */
    public function shouldReturnAPromiseForAPromisedRejectionValue()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        // Both the returned promise, and the deferred's own promise should
        // be rejected with the same value
        $d
            ->resolver()
            ->resolve(When::reject(1))
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldInvokeNewlyAddedCallbackWhenAlreadyResolved()
    {
        $d = new Deferred();
        $d
            ->resolver()
            ->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->promise()
            ->then($mock, $this->expectCallableNever());
    }
}
