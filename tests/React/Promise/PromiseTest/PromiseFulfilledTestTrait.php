<?php

namespace React\Promise\PromiseTest;

trait PromiseFulfilledTestTrait
{
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function thenShouldForwardResultWhenCallbackIsNull()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $resolve(1);
        $promise()
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
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $resolve(1);
        $promise()
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
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $resolve(1);
        $promise()
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
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $resolve(1);
        $promise()
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
        extract($this->getPromiseTestAdapter());

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

        $resolve(1);
        $promise()
            ->then(
                $mock,
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }
}
