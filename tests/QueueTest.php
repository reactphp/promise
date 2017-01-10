<?php

namespace React\Promise;

class QueueTest extends TestCase
{
    /** @test */
    public function canSetDriver()
    {
        $oldDriver = Queue::getDriver();

        $newDriver = $this
            ->getMockBuilder('React\Promise\Queue\DriverInterface')
            ->getMock();

        Queue::setDriver($newDriver);

        $this->assertSame($newDriver, Queue::getDriver());

        Queue::setDriver($oldDriver);
    }
}
