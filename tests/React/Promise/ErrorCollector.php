<?php

namespace React\Promise;

class ErrorCollector
{
    private $errors = array();

    public function register()
    {
        $errors = array();

        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) use (&$errors) {
            $errors[] = compact('errno', 'errstr', 'errfile', 'errline', 'errcontext');
        });

        $this->errors = &$errors;
    }

    public function unregister()
    {
        $this->errors = array();
        restore_error_handler();
    }

    public function assertCollectedError($errstr, $errno)
    {
        foreach ($this->errors as $error) {
            if ($error['errstr'] === $errstr && $error['errno'] === $errno) {
                return;
            }
        }

        $message = 'Error with level ' . $errno . ' and message "' . $errstr . '" not found in ' . var_export($this->errors, true);

        throw new \PHPUnit_Framework_AssertionFailedError($message);
    }
}
