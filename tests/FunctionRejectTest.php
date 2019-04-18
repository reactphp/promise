<?php

namespace React\Promise;

class FunctionRejectTest extends TestCase
{
    /** @test */
    public function shouldRejectAnException()
    {
        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        reject($exception)
            ->then($this->expectCallableNever(), $mock);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowWhenCalledWithANonException()
    {
        reject(1);
    }
}
