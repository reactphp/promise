<?php

namespace Promise;

class Promise implements PromiseInterface
{
    /**
     * @var callable
     */
    private $thenCallback;

    public function __construct($thenCallback)
    {
        if (!is_callable($thenCallback)) {
            throw new \InvalidArgumentException('$thenCallback is not a callable');
        }

        $this->thenCallback = $thenCallback;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return call_user_func($this->thenCallback, $fulfilledHandler, $errorHandler, $progressHandler);
    }
}
