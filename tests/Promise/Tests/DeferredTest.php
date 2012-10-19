<?php

namespace Promise\Tests;

use Promise\Deferred;

/**
 * @group Deferred
 */
class DeferredTest extends TestCase
{
    public function testShouldReturnAPromiseForPassedInResolutionValueWhenAlreadyResolved()
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

    public function testShouldReturnAPromiseForPassedInRejectionValueWhenAlreadyResolved()
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

    public function testShouldReturnSilentlyOnProgressWhenAlreadyResolved()
    {
        $d = new Deferred();
        $d->resolve(1);

        $this->assertNull($d->progress());
    }

    public function testShouldReturnAPromiseForPassedInResolutionValueWhenAlreadyRejected()
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

    public function testShouldReturnAPromiseForPassedInRejectionValueWhenAlreadyRejected()
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

    public function testShouldReturnSilentlyOnProgressWhenAlreadyRejected()
    {
        $d = new Deferred();
        $d->reject(1);

        $this->assertNull($d->progress());
    }
}
