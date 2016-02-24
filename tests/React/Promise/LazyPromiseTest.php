<?php

namespace React\Promise;

/**
 * @group Promise
 * @group LazyPromise
 */
class LazyPromiseTest extends TestCase
{
    /** @test */
    public function shouldNotCallFactoryIfThenIsNotInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->never())
            ->method('__invoke');

        new LazyPromise($factory);
    }

    /** @test */
    public function shouldCallFactoryIfThenIsInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke');

        $p = new LazyPromise($factory);
        $p->then();
    }

    /** @test */
    public function shouldReturnPromiseFromFactory()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(new FulfilledPromise(1)));

        $fulfilledHandler = $this->createCallableMock();
        $fulfilledHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $p = new LazyPromise($factory);

        $p->then($fulfilledHandler);
    }

    /** @test */
    public function shouldReturnPromiseIfFactoryReturnsNull()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(null));

        $p = new LazyPromise($factory);
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then());
    }

    /** @test */
    public function shouldReturnRejectedPromiseIfFactoryThrowsException()
    {
        $exception = new \Exception();

        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $errorHandler = $this->createCallableMock();
        $errorHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $p = new LazyPromise($factory);

        $p->then($this->expectCallableNever(), $errorHandler);
    }

    /** @test */
    public function shouldInvokeCancellationHandlerAndStayPendingWhenCallingCancel()
    {
        $once = $this->expectCallableOnce();

        $factory = function () use ($once){
            return new Deferred($once);
        };

        $p = new LazyPromise($factory);
        $p->cancel();

        $p->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /** @test */
    public function shouldNotInvokeCancellationHandlerIfPromiseIsNotCancellable()
    {
        $mock = $this->getMock('React\\Promise\\PromiseInterface');

        $factory = function () use ($mock){
            return $mock;
        };

        $p = new LazyPromise($factory);
        $p->cancel();

        $p->then($this->expectCallableNever(), $this->expectCallableNever());
    }
}
