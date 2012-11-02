<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenReject
 */
class WhenRejectTest extends TestCase
{
    /** @test */
    public function shouldRejectAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        When::reject($expected)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectAResolvedPromise()
    {
        $expected = 123;

        $d = new Deferred();
        $d->resolve($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        When::reject($d->promise())
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectARejectedPromise()
    {
        $expected = 123;

        $d = new Deferred();
        $d->reject($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        When::reject($d->promise())
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
