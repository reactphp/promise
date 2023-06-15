<?php

namespace React\Promise;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function expectCallableExactly(int $amount): callable
    {
        $mock = $this->createCallableMock();
        $mock->expects(self::exactly($amount))->method('__invoke');
        assert(is_callable($mock));

        return $mock;
    }

    public function expectCallableOnce(): callable
    {
        $mock = $this->createCallableMock();
        $mock->expects(self::once())->method('__invoke');
        assert(is_callable($mock));

        return $mock;
    }

    public function expectCallableNever(): callable
    {
        $mock = $this->createCallableMock();
        $mock->expects(self::never())->method('__invoke');
        assert(is_callable($mock));

        return $mock;
    }

    /** @return MockObject&callable */
    protected function createCallableMock(): MockObject
    {
        $builder = $this->getMockBuilder(\stdClass::class);
        if (method_exists($builder, 'addMethods')) {
            // PHPUnit 9+
            $mock = $builder->addMethods(['__invoke'])->getMock();
        } else {
            // legacy PHPUnit 4 - PHPUnit 9
            $mock = $builder->setMethods(['__invoke'])->getMock();
        }
        assert($mock instanceof MockObject && is_callable($mock));

        return $mock;
    }
}
