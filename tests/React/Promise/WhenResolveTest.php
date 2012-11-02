<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenResolve
 */
class WhenResolveTest extends TestCase
{
    /** @test */
    public function shouldResolveAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        When::resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldResolveAResolvedPromise()
    {
        $expected = 123;

        $d = new Deferred();
        $d->resolve($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        When::resolve($d->promise())
            ->then(
                $mock,
                $this->expectCallableNever()
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

        When::resolve($d->promise())
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = When::resolve(When::resolve($d->then(function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = function ($val) {
                return $val;
            };

            return When::resolve($d->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }
}
