<?php

namespace React\Promise\PromiseTest;

trait PromiseFulfilledTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function fulfilledPromiseShouldBeImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->resolve(2);

        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function fulfilledPromiseShouldInvokeNewlyAddedCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());
    }

    /** @test */
    public function thenShouldForwardResultWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->promise()
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
    public function thenShouldForwardCallbackResultToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
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
    public function thenShouldForwardPromisedCallbackResultValueToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return \React\Promise\resolve($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return \React\Promise\reject($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackThrows()
    {
        $adapter = $this->getPromiseTestAdapter();

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

        $adapter->resolve(1);
        $adapter->promise()
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
    public function doneShouldInvokeFulfillmentHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done($mock));
    }

    /** @test */
    public function doneShouldThrowExceptionThrownFulfillmentHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(function() {
            throw new \Exception('UnhandledRejectionException');
        }));
    }

    /** @test */
    public function doneShouldThrowUnhandledRejectionExceptionWhenFulfillmentHandlerRejectsForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('React\\Promise\\UnhandledRejectionException');

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(function() {
            return \React\Promise\reject();
        }));
    }

    /** @test */
    public function otherwiseShouldNotInvokeRejectionHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }
}
