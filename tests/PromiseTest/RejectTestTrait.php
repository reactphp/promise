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
    public function rejectShouldRejectWithAnException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

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
    public function rejectShouldThrowWhenCalledWithAnImmediateValue()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);
    }

    /** @test */
    public function rejectShouldThrowWhenCalledWithAFulfilledPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(Promise\resolve(1));
    }

    /** @test */
    public function rrejectShouldThrowWhenCalledWithARejectedPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(Promise\reject(1));
    }

    /** @test */
    public function rejectShouldForwardReasonWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

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

        $exception1 = new \Exception();
        $exception2 = new \Exception();
        $exception3 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->promise()
            ->then(null, function ($value) use ($exception3, $adapter) {
                $adapter->reject($exception3);

                return Promise\reject($value);
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

        $exception = new \Exception();

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

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $this->assertNull($adapter->promise()->done(null, $mock));
        $adapter->reject($exception);
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
        $adapter->reject(new \Exception());

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection', $errors[0]['errstr']);
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
        $adapter->reject(new \Exception());

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
        $adapter->reject(new \Exception());
        $d->reject(new \Exception('Unhandled Rejection'));

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains('Unhandled Rejection', $errors[0]['errstr']);
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
