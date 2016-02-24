<?php

namespace React\Promise;

/**
 * @group Promise
 * @group RejectedPromise
 */
class RejectedPromiseTest extends TestCase
{
    /** @test */
    public function shouldReturnAPromise()
    {
        $p = new RejectedPromise();
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then());
    }

    /** @test */
    public function shouldReturnAllowNull()
    {
        $p = new RejectedPromise();
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then(null, null, null));
    }

    /** @test */
    public function shouldForwardUndefinedRejectionValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(null);

        $p = new RejectedPromise(1);
        $p
            ->then(
                $this->expectCallableNever(),
                function () {
                    // Presence of rejection handler is enough to switch back
                    // to resolve mode, even though it returns undefined.
                    // The ONLY way to propagate a rejection is to re-throw or
                    // return a rejected promise;
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new RejectedPromise(1);
        $p
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return $val + 1;
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new RejectedPromise(1);
        $p
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return new FulfilledPromise($val + 1);
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldPropagateRejectionsWhenErrbackThrows()
    {
        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $p = new RejectedPromise(1);
        $p
            ->then(
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }

    /** @test */
    public function shouldPropagateRejectionsWhenErrbackReturnsARejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new RejectedPromise(1);
        $p
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return new RejectedPromise($val + 1);
                }
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldNotBeAffectedByCancellation()
    {
        $p = new RejectedPromise(1);
        $p->cancel();
        $p->then($this->expectCallableNever(), $this->expectCallableOnce());
    }
}
