<?php

namespace React\Promise;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function toClosure(callable $callable)
    {
        if ($callable instanceof \Closure) {
            return $callable;
        }

        return function () use ($callable) {
            return call_user_func_array($callable, func_get_args());
        };
    }

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
}
