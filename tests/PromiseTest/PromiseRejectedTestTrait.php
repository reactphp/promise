<?php

namespace React\Promise\PromiseTest;

use Exception;
use InvalidArgumentException;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use function React\Promise\resolve;

trait PromiseRejectedTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function rejectedPromiseShouldBeImmutable(): void
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
    public function rejectedPromiseShouldInvokeNewlyAddedCallback(): void
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
    public function shouldForwardUndefinedRejectionValue(): void
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
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate(): void
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
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution(): void
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
    public function shouldPropagateRejectionsWhenErrbackThrows(): void
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
    public function shouldPropagateRejectionsWhenErrbackReturnsARejection(): void
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
    public function catchShouldInvokeRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()->catch($mock);
    }

    /** @test */
    public function catchShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise(): void
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
            ->catch(function ($reason) use ($mock) {
                $mock($reason);
            });
    }

    /** @test */
    public function catchShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise(): void
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
            ->catch(function (InvalidArgumentException $reason) use ($mock) {
                $mock($reason);
            });
    }

    /** @test */
    public function catchShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->catch(function (InvalidArgumentException $reason) use ($mock) {
                $mock($reason);
            })->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
    }

    /** @test */
    public function finallyShouldNotSuppressRejectionForRejectedPromise(): void
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
            ->finally(function () {})
            ->then(null, $mock);
    }

    /** @test */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise(): void
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
            ->finally(function (): int { // @phpstan-ignore-line
                return 1;
            })
            ->then(null, $mock);
    }

    /** @test */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise(): void
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
            ->finally(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then(null, $mock);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerThrowsForRejectedPromise(): void
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
            ->finally(function () use ($exception2) {
                throw $exception2;
            })
            ->then(null, $mock);
    }

    /** @test */
    public function finallyShouldRejectWhenHandlerRejectsForRejectedPromise(): void
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
            ->finally(function () use ($exception2) {
                return reject($exception2);
            })
            ->then(null, $mock);
    }

    /** @test */
    public function cancelShouldHaveNoEffectForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->reject(new Exception());

        $adapter->promise()->cancel();
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeRejectionHandlerForRejectedPromise(): void
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

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise(): void
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

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise(): void
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

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function (InvalidArgumentException $reason) use ($mock) {
                $mock($reason);
            })->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionForRejectedPromise(): void
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

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise(): void
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
        ->finally(function (): int { // @phpstan-ignore-line
            return 1;
        })
        ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise(): void
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
            ->always(function (): PromiseInterface { // @phpstan-ignore-line
                return resolve(1);
            })
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerThrowsForRejectedPromise(): void
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

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerRejectsForRejectedPromise(): void
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
}
