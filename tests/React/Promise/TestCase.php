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

    public function typesDataProvider()
    {
        return array(
            array('',            'empty string'),
            array(true,          'true'),
            array(false,         'false'),
            array(new \stdClass, 'object'),
            array(1,             'truthy'),
            array(0,             'falsey')
        );
    }
}
