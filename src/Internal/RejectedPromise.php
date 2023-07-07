<?php

namespace React\Promise\Internal;

use React\Promise\PromiseInterface;
use function React\Promise\_checkTypehint;
use function React\Promise\resolve;

/**
 * @internal
 */
final class RejectedPromise implements PromiseInterface
{
    /** @var \Throwable */
    private $reason;

    /** @var bool */
    private $handled = false;

    /**
     * @param \Throwable $reason
     */
    public function __construct(\Throwable $reason)
    {
        $this->reason = $reason;
    }

    public function __destruct()
    {
        if ($this->handled) {
            return;
        }

        $message = 'Unhandled promise rejection with ' . \get_class($this->reason) . ': ' . $this->reason->getMessage() . ' in ' . $this->reason->getFile() . ':' . $this->reason->getLine() . PHP_EOL;
        $message .= 'Stack trace:' . PHP_EOL . $this->reason->getTraceAsString();

        \error_log($message);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null): PromiseInterface
    {
        if (null === $onRejected) {
            return $this;
        }

        $this->handled = true;

        try {
            return resolve($onRejected($this->reason));
        } catch (\Throwable $exception) {
            return new RejectedPromise($exception);
        }
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
        $this->handled = true;
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
