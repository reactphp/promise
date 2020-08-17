<?php

namespace React\Promise;

use PHPUnit\Framework\TestCase as BaseTestCase;

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

    protected function createCallableMock()
    {
        if (method_exists('PHPUnit\Framework\MockObject\MockBuilder', 'addMethods')) {
            // PHPUnit 9+
            return $this->getMockBuilder('stdClass')->addMethods(array('__invoke'))->getMock();
        } else {
            // legacy PHPUnit 4 - PHPUnit 9
            return $this->getMockBuilder('stdClass')->setMethods(array('__invoke'))->getMock();
        }
    }
}
