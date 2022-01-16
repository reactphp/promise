<?php

namespace React\Promise\Internal;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use function React\Promise\_checkTypehint;
use function React\Promise\enqueue;
use function React\Promise\fatalError;
use function React\Promise\resolve;

/**
 * @internal
 */
final class RejectedPromise implements PromiseInterface
{
    private $reason;

    public function __construct(\Throwable $reason)
    {
        $this->reason = $reason;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null): PromiseInterface
    {
        if (null === $onRejected) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onRejected): void {
            enqueue(function () use ($resolve, $reject, $onRejected): void {
                try {
                    $resolve($onRejected($this->reason));
                } catch (\Throwable $exception) {
                    $reject($exception);
                }
            });
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null): void
    {
        enqueue(function () use ($onRejected) {
            if (null === $onRejected) {
                return fatalError($this->reason);
            }

            try {
                $result = $onRejected($this->reason);
            } catch (\Throwable $exception) {
                return fatalError($exception);
            }

            if ($result instanceof self) {
                return fatalError($result->reason);
            }

            if ($result instanceof PromiseInterface) {
                $result->done();
            }
        });
    }

    public function catch(callable $onRejected): PromiseInterface
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then(null, function (\Throwable $reason) use ($onFulfilledOrRejected): PromiseInterface {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason): PromiseInterface {
                return new RejectedPromise($reason);
            });
        });
    }

    public function cancel(): void
    {
    }

    /**
     * @deprecated 3.0.0 Use `catch()` instead
     * @see self::catch()
     */
    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->catch($onRejected);
    }

    /**
     * @deprecated 3.0.0 Use `always()` instead
     * @see self::always()
     */
    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->finally($onFulfilledOrRejected);
    }
}
