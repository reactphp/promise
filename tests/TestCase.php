<?php

namespace React\Promise;

use PHPUnit\Framework\TestCase as BaseTestCase;
use React\Promise\Stub\CallableStub;

class TestCase extends BaseTestCase
{
    public function expectCallableExactly($amount): callable
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::exactly($amount))
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableOnce(): callable
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableNever(): callable
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::never())
            ->method('__invoke');

        return $mock;
    }

    public function createCallableMock()
    {
        return $this
            ->getMockBuilder(CallableStub::class)
            ->getMock();
    }
}
