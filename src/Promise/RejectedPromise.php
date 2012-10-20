<?php

namespace Promise;

class RejectedPromise implements PromiseInterface
{
    /**
     * @var mixed
     */
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            if (!$errorHandler) {
                return new RejectedPromise($this->value);
            }

            return Util::resolve(call_user_func($errorHandler, $this->value));
        } catch (\Exception $e) {
            return new RejectedPromise($e);
        }
    }
}
