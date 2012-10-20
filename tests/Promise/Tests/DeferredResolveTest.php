<?php

namespace Promise\Tests;

use Promise\Deferred;
use Promise\Util;

use Promise\Tests\Stub\FakeResolvedPromise;
use Promise\Tests\Stub\FakeRejectedPromise;

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

        $d->resolve(1);
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

        $d->resolve(new FakeResolvedPromise(1));
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

        $d->resolve(new FakeRejectedPromise(1));
    }

    /** @test */
    public function shouldReturnAPromiseForTheResolutionValue()
    {
        $d = new Deferred();

        $self = $this;

        $d
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
            ->resolve(Util::normalize(1))
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
        $d->resolve(1);

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
