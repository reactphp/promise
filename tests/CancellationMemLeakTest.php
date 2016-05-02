<?php
namespace React\Promise;

class CancellationMemLeakTest extends TestCase
{
    public function testCancelCleansUp()
    {
        $mainDeferred = new Deferred;

        $memoryUsage = memory_get_usage();

        for ($i = 0; $i < 100000; $i++) {
            $innerDeferred = new Deferred;
            race([$mainDeferred->promise()->then(), $innerDeferred->promise()]);
            $innerDeferred->resolve();
        }

        $this->assertLessThan(2 * $memoryUsage, memory_get_usage());
    }
}
