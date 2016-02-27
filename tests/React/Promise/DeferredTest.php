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

    /** @test */
    public function shouldIgnoreCancellationWithNoCancellationHandlerAndStayPending()
    {
        $d = new Deferred();
        $d->cancel();

        $d->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldIgnoreCancellationWhenAlreadySettled()
    {
        $d = new Deferred($this->expectCallableNever());
        $d->resolve();

        $d->cancel();

        $d->then($this->expectCallableOnce(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldInvokeCancellationHandlerAndStayPendingWhenCallingCancel()
    {
        $d = new Deferred($this->expectCallableOnce());
        $d->cancel();

        $d->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldInvokeCancellationHandlerOnlyOnceWhenCallingCancelMultipleTimes()
    {
        $d = new Deferred($this->expectCallableOnce());
        $d->cancel();
        $d->cancel();
    }

    /** @test */
    public function shouldResolveWhenCancellationHandlerResolves()
    {
        $d = new Deferred(function ($resolve) {
            $resolve();
        });

        $d->cancel();

        $d->then($this->expectCallableOnce(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldRejectWhenCancellationHandlerRejects()
    {
        $d = new Deferred(function ($_, $reject) {
            $reject();
        });

        $d->cancel();

        $d->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    /** @test */
    public function shouldRejectWhenCancellationHandlerThrows()
    {
        $d = new Deferred(function () {
            throw new \Exception();
        });

        $d->cancel();

        $d->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    /** @test */
    public function shouldProgressWhenCancellationHandlerEmitsProgress()
    {
        $d = new Deferred(function ($_, $__, $progress) {
            $progress();
        });

        $d->then(null, null, $this->expectCallableOnce());

        $d->cancel();
    }

    /** @test */
    public function shouldInvokeCancellationHandleWhenCancellingDerived()
    {
        $d = new Deferred($this->expectCallableOnce());

        $p = $d->then();
        $p->cancel();
    }

    /** @test */
    public function shouldNotInvokeCancellationHandleWhenCancellingNotAllDerived()
    {
        $d = new Deferred($this->expectCallableNever());

        $p1 = $d->then();
        $p2 = $d->then();

        $p1->cancel();
    }

    /** @test */
    public function shouldInvokeCancellationHandleWhenCancellingAllDerived()
    {
        $d = new Deferred($this->expectCallableOnce());

        $p1 = $d->then();
        $p2 = $d->then();

        $p1->cancel();
        $p2->cancel();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function shouldThrowIfCancellerIsNotACallable()
    {
        new Deferred(false);
    }
}
