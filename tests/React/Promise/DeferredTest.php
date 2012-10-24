<?php

namespace React\Promise;

/**
 * @group Deferred
 */
class DeferredTest extends TestCase
{
    /** @test */
    public function shouldReturnAPromiseForPassedInResolutionValueWhenAlreadyResolved()
    {
        $d = new Deferred();
        $d->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d->resolve(2)->then($mock);
    }

    /** @test */
    public function shouldReturnAPromiseForPassedInRejectionValueWhenAlreadyResolved()
    {
        $d = new Deferred();
        $d->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d->reject(2)->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldReturnSilentlyOnProgressWhenAlreadyResolved()
    {
        $d = new Deferred();
        $d->resolve(1);

        $this->assertNull($d->progress());
    }

    /** @test */
    public function shouldReturnAPromiseForPassedInResolutionValueWhenAlreadyRejected()
    {
        $d = new Deferred();
        $d->reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d->resolve(2)->then($mock);
    }

    /** @test */
    public function shouldReturnAPromiseForPassedInRejectionValueWhenAlreadyRejected()
    {
        $d = new Deferred();
        $d->reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d->reject(2)->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldReturnSilentlyOnProgressWhenAlreadyRejected()
    {
        $d = new Deferred();
        $d->reject(1);

        $this->assertNull($d->progress());
    }
}
