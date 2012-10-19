<?php

namespace Promise\Tests;

use Promise\Deferred;

/**
 * @group Deferred
 * @group DeferredProgress
 */
class DeferredProgressTest extends TestCase
{
    /** @test */
    public function shouldProgress()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        $d
            ->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $d->progress($sentinel);
    }

    /** @test */
    public function shouldPropagateProgressToDownstreamPromises()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnArgument(0));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        $d
            ->promise()
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

        $d->progress($sentinel);
    }

    /** @test */
    public function shouldPropagateTransformedProgressToDownstreamPromises()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        $d
            ->promise()
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

        $d->progress(1);
    }

    /** @test */
    public function shouldPropagateCaughtExceptionValueAsProgress()
    {
        $exception = new \Exception();

        $d = new Deferred();

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

        $d
            ->promise()
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

        $d->progress(1);
    }

    /** @test */
    public function shouldForwardProgressEventsWhenIntermediaryCallbackTiedToAResolvedPromiseReturnsAPromise()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();
        $d2 = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        // resolve $d BEFORE calling attaching progress handler
        $d->resolve();

        $d
            ->promise()
            ->then(function() use ($d2) {
                return $d2->promise();
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        $d2->progress($sentinel);
    }

    /** @test */
    public function shouldForwardProgressEventsWhenIntermediaryCallbackTiedToAnUnresolvedPromiseReturnsAPromise()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();
        $d2 = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        $d
            ->promise()
            ->then(function() use ($d2) {
                return $d2->promise();
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        // resolve $d AFTER calling attaching progress handler
        $d->resolve();
        $d2->progress($sentinel);
    }

    /** @test */
    public function shouldForwardProgressWhenResolvedWithAnotherPromise()
    {
        $sentinel = new \stdClass();

        $d = new Deferred();
        $d2 = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($sentinel));

        $d
            ->promise()
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

        $d->resolve($d2->promise());
        $d2->progress($sentinel);
    }

    /** @test */
    public function shouldAllowResolveAfterProgress()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d
            ->promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
                $mock
            );

        $d->progress(1);
        $d->resolve(2);
    }

    /** @test */
    public function shouldAllowRejectAfterProgress()
    {
        $d = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d
            ->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
                $mock
            );

        $d->progress(1);
        $d->reject(2);
    }
}
