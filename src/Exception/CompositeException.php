<?php

namespace React\Promise\Exception;

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
