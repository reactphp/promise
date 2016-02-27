<?php

namespace React\Promise;

/**
 * @group Promise
 * @group FulfilledPromise
 */
class FulfilledPromiseTest extends TestCase
{
    /** @test */
    public function shouldReturnAPromise()
    {
        $p = new FulfilledPromise();
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then());
    }

    /** @test */
    public function shouldReturnAllowNull()
    {
        $p = new FulfilledPromise();
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then(null, null, null));
    }

    /** @test */
    public function shouldForwardResultWhenCallbackIsNull()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $p = new FulfilledPromise(1);
        $p
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldForwardCallbackResultToNextCallback()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new FulfilledPromise(1);
        $p
            ->then(
                function ($val) {
                    return $val + 1;
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldForwardPromisedCallbackResultValueToNextCallback()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new FulfilledPromise(1);
        $p
            ->then(
                function ($val) {
                    return new FulfilledPromise($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $p = new FulfilledPromise(1);
        $p
            ->then(
                function ($val) {
                    return new RejectedPromise($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldSwitchFromCallbacksToErrbacksWhenCallbackThrows()
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

        $p = new FulfilledPromise(1);
        $p
            ->then(
                $mock,
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }

    /** @test */
    public function shouldNotBeAffectedByCancellation()
    {
        $p = new FulfilledPromise(1);
        $p->cancel();
        $p->then($this->expectCallableOnce());
    }
}
