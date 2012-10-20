<?php

namespace Promise;

class ResolvedPromise implements PromiseInterface
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
            $value = $this->value;
            if ($fulfilledHandler) {
                $value = call_user_func($fulfilledHandler, $value);
            }

            return Util::resolve($value);
        } catch (\Exception $e) {
            return new RejectedPromise($e);
        }
    }
}
