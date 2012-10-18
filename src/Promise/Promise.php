<?php

namespace Promise;

class Promise implements PromiseInterface
{
    /**
     * @var Deferred
     */
    private $deferred;

    public function __construct(Deferred $deferred)
    {
        $this->deferred = $deferred;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
    }

    public function isResolved()
    {
        return $this->deferred->isResolved();
    }

    public function isRejected()
    {
        return $this->deferred->isRejected();
    }

    public static function defer()
    {
        return new Deferred();
    }

    public static function when(array $promisesOrValues)
    {
        $deferred = static::defer();
        $length   = count($promisesOrValues);
        $results  = array();

        $errback = function ($error) use ($deferred) {
            $deferred->reject($error);
        };

        $checkResolve = function () use (&$results, $length, $deferred) {
            if (count($results) === $length) {
                ksort($results);
                call_user_func_array(array($deferred, 'resolve'), $results);
            }
        };

        foreach ($promisesOrValues as $i => $promiseOrValue) {
            if (is_callable($promiseOrValue)) {
                try {
                    $promiseOrValue = call_user_func($promiseOrValue);
                } catch (\Exception $e) {
                    $errback($e);
                    continue;
                }
            }

            $callback = function ($value = null) use (&$results, $i, $checkResolve) {
                $results[$i] = $value;
                $checkResolve();
            };

            if ($promiseOrValue instanceof PromiseInterface) {
                $promiseOrValue->then($callback, $errback);
            } else {
                $callback($promiseOrValue);
            }
        }

        return $deferred->promise();
    }
}
