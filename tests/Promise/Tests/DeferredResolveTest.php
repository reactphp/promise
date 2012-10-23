<?php

namespace Promise\Tests;

use Promise\Deferred;
use Promise\RejectedPromise;
use Promise\ResolvedPromise;
use Promise\Util;

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
            ->resolve(new ResolvedPromise(1));
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

        $self = $this;

        $d
            ->resolver()
            ->resolve(1)
            ->then(function ($returnedPromiseVal) use ($d, $self) {
                $mock = $self->createCallableMock();
                $mock
                    ->expects($self->once())
                    ->method('__invoke')
                    ->with($self->identicalTo($returnedPromiseVal));

                $d->then($mock);
            });
    }

    /** @test */
    public function shouldReturnAPromiseForAPromisedResolutionValue()
    {
        $d = new Deferred();

        $self = $this;

        $d
            ->resolver()
            ->resolve(Util::resolve(1))
            ->then(function ($returnedPromiseVal) use ($d, $self) {
                $mock = $self->createCallableMock();
                $mock
                    ->expects($self->once())
                    ->method('__invoke')
                    ->with($self->identicalTo($returnedPromiseVal));

                $d->then($mock);
            });
    }

    /** @test */
    public function shouldReturnAPromiseForAPromisedRejectionValue()
    {
        $d = new Deferred();

        $self = $this;

        $d
            ->resolver()
            ->resolve(Util::reject(1))
            ->then($this->expectCallableNever(), function ($returnedPromiseVal) use ($d, $self) {
                $mock = $self->createCallableMock();
                $mock
                    ->expects($self->once())
                    ->method('__invoke')
                    ->with($self->identicalTo($returnedPromiseVal));

                $d->then($self->expectCallableNever(), $mock);
            });
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
