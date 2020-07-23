<?php

namespace React\Tests\Promise;

use React\Promise\When;

/**
 * @group When
 * @group WhenLazy
 */
class WhenLazyTest extends TestCase
{
    /** @test */
    public function shouldReturnALazyPromise()
    {
        $this->assertInstanceOf('React\\Promise\\PromiseInterface',  When::lazy(function () {}));
    }

    /** @test */
    public function shouldCallFactoryIfThenIsInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke');

        When::lazy($factory)
            ->then();
    }
}
