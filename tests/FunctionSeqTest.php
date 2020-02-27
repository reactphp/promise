<?php

namespace React\Promise;

use Exception;

class FunctionSeqTest extends TestCase
{
    /** @test */
    public function shouldResolveOnlyOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke');

        seq([1, 2, 3])
            ->then($mock);
    }
}
