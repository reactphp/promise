<?php

namespace React\Promise\PromiseTest;

use React\Promise;
use React\Promise\Deferred;

trait RejectTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function rejectShouldRejectWithAnImmediateValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(1);
    }

    /** @test */
    public function rejectShouldRejectWithFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\resolve(1));
    }

    /** @test */
    public function rejectShouldRejectWithRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\reject(1));
    }

    /** @test */
    public function rejectShouldForwardReasonWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject(1);
    }

    /** @test */
    public function rejectShouldMakePromiseImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(null, function ($value) use ($adapter) {
                $adapter->reject(3);

                return Promise\reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject(1);
        $adapter->reject(2);
    }

    /** @test */
    public function rejectShouldInvokeOtherwiseHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->otherwise($mock);

        $adapter->reject(1);
    }

    /** @test */
    public function doneShouldInvokeRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, $mock));
        $adapter->reject(1);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionThrownByRejectionHandler()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function () {
            throw new \Exception('Unhandled Rejection');
        }));
        $adapter->reject(1);

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorUnhandledRejectionExceptionWhenRejectedWithNonException()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(1);

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection: 1', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorUnhandledRejectionExceptionWhenRejectionHandlerRejects()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function () {
            return \React\Promise\reject();
        }));
        $adapter->reject(1);

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection: null', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorRejectionExceptionWhenRejectionHandlerRejectsWithException()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function () {
            return \React\Promise\reject(new \Exception('Unhandled Rejection'));
        }));
        $adapter->reject(1);

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorUnhandledRejectionExceptionWhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $d = new Deferred();
        $promise = $d->promise();

        $this->assertNull($adapter->promise()->done(null, function () use ($promise) {
            return $promise;
        }));
        $adapter->reject(1);
        $d->reject(1);

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection: 1', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionProvidedAsRejectionValue()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(new \Exception('Unhandled Rejection'));

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorWithDeepNestingPromiseChains()
    {
        $errorCollector = new Promise\ErrorCollector();
        $errorCollector->start();

        $exception = new \Exception('Unhandled Rejection');

        $d = new Deferred();

        $result = \React\Promise\resolve(\React\Promise\resolve($d->promise()->then(function () use ($exception) {
            $d = new Deferred();
            $d->resolve();

            return \React\Promise\resolve($d->promise()->then(function () {}))->then(
                function () use ($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();

        $d->resolve();

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertEquals((string) $exception, $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldRecoverWhenRejectionHandlerCatchesException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function (\Exception $e) {

        }));
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    /** @test */
    public function alwaysShouldNotSuppressRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

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

        $exception = new \Exception();

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

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return \React\Promise\resolve(1);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /** @test */
    public function alwaysShouldRejectWhenHandlerThrowsForRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

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

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                return \React\Promise\reject($exception);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }
}
