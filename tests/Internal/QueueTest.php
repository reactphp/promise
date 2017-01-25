<?php

namespace React\Promise\Internal;

use React\Promise\TestCase;

class QueueTest extends TestCase
{
    /** @test */
    public function excutesTasks()
    {
        $queue = new Queue();

        $queue->enqueue($this->expectCallableOnce());
        $queue->enqueue($this->expectCallableOnce());
    }

    /** @test */
    public function excutesNestedEnqueuedTasks()
    {
        $queue = new Queue();

        $nested = $this->expectCallableOnce();

        $task = function() use ($queue, $nested) {
            $queue->enqueue($nested);
        };

        $queue->enqueue($task);
    }

    /** @test */
    public function rethrowsExceptionsThrownFromTasks()
    {
        $this->setExpectedException('\Exception', 'test');

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException(new \Exception('test')));

        $queue = new Queue();
        $queue->enqueue($mock);
    }
}
