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
        $builder = $this->getMockBuilder(\stdClass::class);
        if (method_exists($builder, 'addMethods')) {
            // PHPUnit 9+
            return $builder->addMethods(['__invoke'])->getMock();
        } else {
            // legacy PHPUnit 4 - PHPUnit 9
            return $builder->setMethods(['__invoke'])->getMock();
        }
    }
}
