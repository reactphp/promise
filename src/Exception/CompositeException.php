<?php

namespace React\Promise\Exception;

/**
 * Represents an exception that is a composite of one or more other exceptions.
 *
 * This exception is useful in situations where a promise must be rejected
 * with multiple exceptions. It is used for example to reject the returned
 * promise from `some()` and `any()` when too many input promises reject.
 */
class CompositeException extends \Exception
{
    private $throwables;

    public function __construct(array $throwables, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->throwables = $throwables;
    }

    /**
     * @return \Throwable[]
     */
    public function getThrowables(): array
    {
        return $this->throwables;
    }
}
