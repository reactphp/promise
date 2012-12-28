<?php

namespace React\Promise;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private $errors = array();

    public function expectCallableExactly($amount)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->exactly($amount))
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    public function createCallableMock()
    {
        return $this->getMock('React\\Promise\Stub\CallableStub');
    }

    public function typesDataProvider()
    {
        return array(
            'empty string' => array(''),
            'true'         => array(true),
            'false'        => array(false),
            'object'       => array(new \stdClass),
            'truthy'       => array(1),
            'falsey'       => array(0)
        );
    }

    public function setErrorHandler()
    {
        $errors = array();

        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) use (&$errors) {
            $errors[] = compact('errno', 'errstr', 'errfile', 'errline', 'errcontext');
        });

        $this->errors = &$errors;
    }

    public function restoreErrorHandler()
    {
        $this->errors = array();
        restore_error_handler();
    }

    public function assertError($errstr, $errno)
    {
        foreach ($this->errors as $error) {
            if ($error['errstr'] === $errstr && $error['errno'] === $errno) {
                return;
            }
        }

        $this->fail('Error with level ' . $errno . ' and message "' . $errstr . '" not found in ',  var_export($this->errors, true));
    }
}
