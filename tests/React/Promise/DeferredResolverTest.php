<?php

namespace React\Promise;

/**
 * @group Resolver
 * @group DeferredResolver
 */
class DeferredResolverTest extends TestCase
{
    /** @test */
    public function shouldForwardToDeferred()
    {
        $mock = $this->getMock('React\\Promise\\Deferred');
        $mock
            ->expects($this->once())
            ->method('resolve')
            ->with(1);
        $mock
            ->expects($this->once())
            ->method('reject')
            ->with(1);
        $mock
            ->expects($this->once())
            ->method('progress')
            ->with(1);

        $p = new DeferredResolver($mock);
        $p->resolve(1);
        $p->reject(1);
        $p->progress(1);
    }
}
