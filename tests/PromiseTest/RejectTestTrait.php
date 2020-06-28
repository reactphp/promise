<?php

namespace React\Promise\PromiseTest;

use Exception;
use React\Promise;
use React\Promise\Deferred;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use function React\Promise\reject;
use function React\Promise\resolve;

trait RejectTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function rejectShouldRejectWithAnException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function rejectShouldForwardReasonWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject($exception);
    }

    /** @test */
    public function rejectShouldMakePromiseImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new Exception();
        $exception2 = new Exception();
        $exception3 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->promise()
            ->then(null, function ($value) use ($exception3, $adapter) {
                $adapter->reject($exception3);

                return reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject($exception1);
        $adapter->reject($exception2);
    }

    /** @test */
    public function rejectShouldInvokeOtherwiseHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->otherwise($mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function doneShouldInvokeRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        self::assertNull($adapter->promise()->done(null, $mock));
        $adapter->reject($exception);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionThrownByRejectionHandler()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Unhandled Rejection');

        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done(null, function () {
            throw new Exception('Unhandled Rejection');
        }));
        $adapter->reject(new Exception());
    }

    /** @test */
    public function doneShouldTriggerFatalErrorRejectionExceptionWhenRejectionHandlerRejectsWithException()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Unhandled Rejection');

        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done(null, function () {
            return reject(new Exception('Unhandled Rejection'));
        }));
        $adapter->reject(new Exception());
    }

    /** @test */
    public function doneShouldTriggerFatalErrorUnhandledRejectionExceptionWhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Unhandled Rejection');

        $adapter = $this->getPromiseTestAdapter();

        $d = new Deferred();
        $promise = $d->promise();

        self::assertNull($adapter->promise()->done(null, function () use ($promise) {
            return $promise;
        }));
        $adapter->reject(new Exception());
        $d->reject(new Exception('Unhandled Rejection'));
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionProvidedAsRejectionValue()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Unhandled Rejection');

        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done());
        $adapter->reject(new Exception('Unhandled Rejection'));
    }

    /** @test */
    public function doneShouldTriggerFatalErrorWithDeepNestingPromiseChains()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Unhandled Rejection');

        $exception = new Exception('Unhandled Rejection');

        $d = new Deferred();

        $result = resolve(resolve($d->promise()->then(function () use ($exception) {
            $d = new Deferred();
            $d->resolve();

            return resolve($d->promise()->then(function () {}))->then(
                function () use ($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();

        $d->resolve();
    }

    /** @test */
    public function doneShouldRecoverWhenRejectionHandlerCatchesException()
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertNull($adapter->promise()->done(null, function (Exception $e) {

        }));
        $adapter->reject(new Exception('UnhandledRejectionException'));
    }

    /** @test */
    public function alwaysShouldNotSuppressRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function alwaysShouldRejectWhenHandlerThrowsForRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function alwaysShouldRejectWhenHandlerRejectsForRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }
}
