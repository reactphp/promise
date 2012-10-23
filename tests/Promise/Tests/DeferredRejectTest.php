<?php

namespace Promise\Tests;

use Promise\Deferred;

/**
 * @group Deferred
 * @group DeferredReject
 */
class DeferredRejectTest extends TestCase
{
    /** @test */
    public function shouldReject()
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
            ->reject(1);
    }

    /** @test */
    public function shouldReturnAPromiseForTheRejectionValue()
    {
        $d = new Deferred();

        $self = $this;

        $d
            ->resolver()
            ->reject(1)
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
    public function shouldInvokeNewlyAddedErrbackWhenAlreadyRejected()
    {
        $d = new Deferred();
        $d
            ->resolver()
            ->reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->promise()
            ->then($this->expectCallableNever(), $mock);
    }
}
