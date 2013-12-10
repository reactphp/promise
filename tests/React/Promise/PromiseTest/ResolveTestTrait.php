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
