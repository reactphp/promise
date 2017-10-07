<?php

namespace React\Promise;

class FunctionThrottleTest extends TestCase
{

    /** @test */
    public function shouldResolvePromisesArrayWithLessThenConcurrency()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        throttle([resolve(1), resolve(2), resolve(3)], 10)
            ->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArrayWithMultipleOfConcurrency()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3, 4, 5, 6]));

        throttle([resolve(1), resolve(2), resolve(3), resolve(4), resolve(5), resolve(6)], 2)
            ->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArrayWithMoreThenConcurrency()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3, 4, 5, 6, 7]));

        throttle([resolve(1), resolve(2), resolve(3), resolve(4), resolve(5), resolve(6), resolve(7)], 2)
            ->then($mock);
    }
}
