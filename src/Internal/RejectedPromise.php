<?php

namespace React\Promise\Internal;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Throwable;
use function React\Promise\_checkTypehint;
use function React\Promise\enqueue;
use function React\Promise\fatalError;
use function React\Promise\resolve;
use function React\Promise\unhandledException;

/**
 * @internal
 */
final class RejectedPromise implements PromiseInterface
{
    private $reason;
    private $handled = false;

    public function __construct(Throwable $reason)
    {
        $this->reason = $reason;
    }

    public function __destruct()
    {
        if ($this->handled) {
            return;
        }

        unhandledException($this->reason);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null): PromiseInterface
    {
        if (null === $onRejected) {
            return $this;
        }

        $this->handled = true;

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
            $this->handled = true;

            if (null === $onRejected) {
                unhandledException($this->reason);
            }

            try {
                $result = $onRejected($this->reason);
            } catch (\Throwable $exception) {
                unhandledException($exception);
            }

            if ($result instanceof self) {
                unhandledException($result->reason);
            }

            if ($result instanceof PromiseInterface) {
                $result->done();
            }
        });
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected): PromiseInterface
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
}
