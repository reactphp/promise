<?php

namespace React\Promise;

class TestCase extends \PHPUnit_Framework_TestCase
{
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

    public function invalidCallbackDataProvider()
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
}
