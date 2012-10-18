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

    public static function when()
    {
        $deferred = static::defer();
        $args     = func_get_args();
        $numArgs  = func_num_args();
        $results  = array();

        $errback = function ($error) use ($deferred) {
            $deferred->reject($error);
        };

        $checkResolve = function () use (&$results, $numArgs, $deferred) {
            if (count($results) === $numArgs) {
                ksort($results);
                $deferred->resolve($results);
            }
        };

        for ($i = 0; $i < $numArgs; $i++) {
            $arg = $args[$i];

            $callback = function ($value = null) use (&$results, $i, $checkResolve) {
                $results[$i] = $value;
                $checkResolve();
            };

            if ($arg instanceof PromiseInterface) {
                $arg->then($callback, $errback);
            } elseif (is_callable($arg)) {
                try {
                    $callback(call_user_func($arg));
                } catch (\Exception $e) {
                    $errback($e);
                }
            } else {
                $callback($arg);
            }
        }

        return $deferred->promise();
    }
}
