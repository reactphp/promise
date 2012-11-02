<?php

namespace React\Promise;

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

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $d
            ->resolver()
            ->reject(1)
            ->then($this->expectCallableNever(), $mock);
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
