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
    private $exceptions;

    public function __construct(array $exceptions, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->exceptions = $exceptions;
    }

    /**
     * @return \Throwable[]|\Exception[]
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    public static function tooManyPromisesRejected(array $reasons)
    {
        return new self(
            $reasons,
            'Too many promises rejected.'
        );
    }
}
