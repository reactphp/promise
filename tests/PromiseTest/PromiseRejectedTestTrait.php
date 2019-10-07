<?php

namespace React\Promise\PromiseTest;

use Exception;
use InvalidArgumentException;
use React\Promise\Deferred;
use React\Promise\ErrorCollector;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use function React\Promise\reject;
use function React\Promise\resolve;

trait PromiseRejectedTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function rejectedPromiseShouldBeImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new Exception();
        $exception2 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->reject($exception1);
        $adapter->reject($exception2);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function rejectedPromiseShouldInvokeNewlyAddedCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $adapter->reject($exception);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldForwardUndefinedRejectionValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(null);

        $adapter->reject(new Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function () {
                    // Presence of rejection handler is enough to switch back
                    // to resolve mode, even though it returns undefined.
                    // The ONLY way to propagate a rejection is to re-throw or
                    // return a rejected promise;
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(new Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function () {
                    return 2;
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(new Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function () {
                    return resolve(2);
                }
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldPropagateRejectionsWhenErrbackThrows()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

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

        $adapter->reject(new Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }

    /** @test */
    public function shouldPropagateRejectionsWhenErrbackReturnsARejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject(new Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function () use ($exception) {
                    return reject($exception);
                }
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function doneShouldInvokeRejectionHandlerForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        self::assertNull($adapter->promise()->done(null, $mock));
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionThrownByRejectionHandlerForRejectedPromise()
    {
        $errorCollector = new ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new Exception());
        self::assertNull($adapter->promise()->done(null, function () {
            throw new Exception('Unhandled Rejection');
        }));

        $errors = $errorCollector->stop();

        self::assertEquals(E_USER_ERROR, $errors[0]['errno']);
        self::assertContains('Unhandled Rejection', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorRejectionExceptionWhenRejectionHandlerRejectsWithExceptionForRejectedPromise()
    {
        $errorCollector = new ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new Exception());
        self::assertNull($adapter->promise()->done(null, function () {
            return reject(new Exception('Unhandled Rejection'));
        }));

        $errors = $errorCollector->stop();

        self::assertEquals(E_USER_ERROR, $errors[0]['errno']);
        self::assertContains('Unhandled Rejection', $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorExceptionProvidedAsRejectionValueForRejectedPromise()
    {
        $errorCollector = new ErrorCollector();
        $errorCollector->start();

        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception('Unhandled Rejection');

        $adapter->reject($exception);
        self::assertNull($adapter->promise()->done());

        $errors = $errorCollector->stop();

        self::assertEquals(E_USER_ERROR, $errors[0]['errno']);
        self::assertEquals((string) $exception, $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldTriggerFatalErrorWithDeepNestingPromiseChainsForRejectedPromise()
    {
        $errorCollector = new ErrorCollector();
        $errorCollector->start();

        $exception = new Exception('UnhandledRejectionException');

        $d = new Deferred();
        $d->resolve();

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

        $errors = $errorCollector->stop();

        self::assertEquals(E_USER_ERROR, $errors[0]['errno']);
        self::assertEquals((string) $exception, $errors[0]['errstr']);
    }

    /** @test */
    public function doneShouldRecoverWhenRejectionHandlerCatchesExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new Exception('UnhandledRejectionException'));
        self::assertNull($adapter->promise()->done(null, function (Exception $e) {

        }));
    }

    /** @test */
    public function otherwiseShouldInvokeRejectionHandlerForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()->otherwise($mock);
    }

    /** @test */
    public function otherwiseShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function ($reason) use ($mock) {
                $mock($reason);
            });
    }

    /** @test */
    public function otherwiseShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new InvalidArgumentException();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function (InvalidArgumentException $reason) use ($mock) {
                $mock($reason);
            });
    }

    /** @test */
    public function otherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function (InvalidArgumentException $reason) use ($mock) {
                $mock($reason);
            });
    }

    /** @test */
    public function alwaysShouldNotSuppressRejectionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {})
            ->then(null, $mock);
    }

    /** @test */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then(null, $mock);
    }

    /** @test */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then(null, $mock);
    }

    /** @test */
    public function alwaysShouldRejectWhenHandlerThrowsForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new Exception();
        $exception2 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(function () use ($exception2) {
                throw $exception2;
            })
            ->then(null, $mock);
    }

    /** @test */
    public function alwaysShouldRejectWhenHandlerRejectsForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new Exception();
        $exception2 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(function () use ($exception2) {
                return reject($exception2);
            })
            ->then(null, $mock);
    }

    /** @test */
    public function cancelShouldReturnNullForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new Exception());

        self::assertNull($adapter->promise()->cancel());
    }

    /** @test */
    public function cancelShouldHaveNoEffectForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->reject(new Exception());

        $adapter->promise()->cancel();
    }
}
