<?php

namespace Promise;

/**
 * A Promise in rejected state.
 */
class RejectedPromise implements PromiseInterface
{
    /**
     * @var mixed
     */
    private $error;

    /**
     * Constructor
     *
     * @param mixed $error
     */
    public function __construct($error = null)
    {
        $this->error = $error;
    }

    /**
     * {@inheritDoc}
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        try {
            if (!$errorHandler) {
                return new RejectedPromise($this->error);
            }

            return Util::resolve(call_user_func($errorHandler, $this->error));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
