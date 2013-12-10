<?php

namespace React\Promise\PromiseTest;

trait ProgressTestTrait
{
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function progressShouldProgress()
    {
        extract($this->getPromiseTestAdapter());

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $progress($sentinel);
    }

    /** @test */
    public function progressShouldPropagateProgressToDownstreamPromises()
    {
        extract($this->getPromiseTestAdapter());

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnArgument(0));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $progress($sentinel);
    }

    /** @test */
    public function progressShouldPropagateTransformedProgressToDownstreamPromises()
    {
        extract($this->getPromiseTestAdapter());

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $progress(1);
    }

    /** @test */
    public function progressShouldPropagateCaughtExceptionValueAsProgress()
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

        $promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $progress(1);
    }

    /** @test */
    public function progressShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAResolvedPromiseReturnsAPromise()
    {
        extract($this->getPromiseTestAdapter());
        extract($this->getPromiseTestAdapter(), EXTR_PREFIX_ALL, 'other');

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        // resolve BEFORE attaching progress handler
        $resolve();

        $promise()
            ->then(function () use ($other_promise) {
                return $other_promise();
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        $other_progress($sentinel);
    }

    /** @test */
    public function progressShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAnUnresolvedPromiseReturnsAPromise()
    {
        extract($this->getPromiseTestAdapter());
        extract($this->getPromiseTestAdapter(), EXTR_PREFIX_ALL, 'other');

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $promise()
            ->then(function () use ($other_promise) {
                return $other_promise();
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        // resolve AFTER attaching progress handler
        $resolve();
        $other_progress($sentinel);
    }

    /** @test */
    public function progressShouldForwardProgressWhenResolvedWithAnotherPromise()
    {
        extract($this->getPromiseTestAdapter());
        extract($this->getPromiseTestAdapter(), EXTR_PREFIX_ALL, 'other');

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $resolve($other_promise());
        $other_progress($sentinel);
    }

    /** @test */
    public function progressShouldAllowResolveAfterProgress()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
                $mock
            );

        $progress(1);
        $resolve(2);
    }

    /** @test */
    public function progressShouldAllowRejectAfterProgress()
    {
        extract($this->getPromiseTestAdapter());

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
                $mock
            );

        $progress(1);
        $reject(2);
    }

    /** @test */
    public function progressShouldReturnSilentlyOnProgressWhenAlreadyResolved()
    {
        extract($this->getPromiseTestAdapter());

        $resolve(1);

        $this->assertNull($progress());
    }

    /** @test */
    public function progressShouldReturnSilentlyOnProgressWhenAlreadyRejected()
    {
        extract($this->getPromiseTestAdapter());

        $reject(1);

        $this->assertNull($progress());
    }
}
