<?php

namespace React\Promise\PromiseTest;

use React\Promise;

trait ResolveTestTrait
{
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function resolveShouldResolve()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($mock);

        $resolve(1);
    }

    /** @test */
    public function resolveShouldResolveWithPromisedValue()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($mock);

        $resolve(Promise\resolve(1));
    }

    /** @test */
    public function resolveShouldRejectWhenResolvedWithRejectedPromise()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($this->expectCallableNever(), $mock);

        $resolve(Promise\reject(1));
    }

    /** @test */
    public function resolveShouldReturnAPromiseForTheResolutionValue()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $resolve(1)
            ->then($mock);
    }

    /** @test */
    public function resolveShouldReturnAPromiseForAPromisedResolutionValue()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $resolve(Promise\resolve(1))
            ->then($mock);
    }

    /** @test */
    public function resolveShouldReturnAPromiseForAPromisedRejectionValue()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        // Both the returned promise, and the deferred's own promise should
        // be rejected with the same value
        $resolve(Promise\reject(1))
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function resolveShouldReturnAPromiseForPassedInResolutionValueWhenAlreadyResolved()
    {
        extract($this->getPromiseTestAdapter());

        $resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $resolve(2)
            ->then($mock);
    }

    /** @test */
    public function resolveShouldReturnAPromiseForPassedInResolutionValueWhenAlreadyRejected()
    {
        extract($this->getPromiseTestAdapter());

        $reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $resolve(2)
            ->then($mock);
    }

    /** @test */
    public function resolveShouldInvokeNewlyAddedCallbackWhenAlreadyResolved()
    {
        extract($this->getPromiseTestAdapter());

        $resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($mock, $this->expectCallableNever());
    }

    /** @test */
    public function resolveShouldForwardValueWhenCallbackIsNull()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );

        $resolve(1);
    }
}
