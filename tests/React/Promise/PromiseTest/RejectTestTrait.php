<?php

namespace React\Promise\PromiseTest;

use React\Promise;

trait RejectTestTrait
{
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function rejectShouldRejectWithAnImmediateValue()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($this->expectCallableNever(), $mock);

        $reject(1);
    }

    /** @test */
    public function rejectShouldRejectWithFulfilledPromise()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($this->expectCallableNever(), $mock);

        $reject(Promise\resolve(1));
    }

    /** @test */
    public function rejectShouldRejectWithRejectedPromise()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($this->expectCallableNever(), $mock);

        $reject(Promise\reject(1));
    }

    /** @test */
    public function rejectShouldInvokeNewlyAddedErrbackWhenAlreadyRejected()
    {
        extract($this->getPromiseTestAdapter());

        $reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function rejectShouldForwardReasonWhenCallbackIsNull()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $reject(1);
    }
}
