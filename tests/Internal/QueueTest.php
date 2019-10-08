<?php

namespace React\Promise\Internal;

use Exception;
use React\Promise\TestCase;

class QueueTest extends TestCase
{
    /** @test */
    public function executesTasks()
    {
        $queue = new Queue();

        $queue->enqueue($this->expectCallableOnce());
        $queue->enqueue($this->expectCallableOnce());
    }

    /** @test */
    public function executesNestedEnqueuedTasks()
    {
        $queue = new Queue();

        $nested = $this->expectCallableOnce();

        $task = function () use ($queue, $nested) {
            $queue->enqueue($nested);
        };

        $queue->enqueue($task);
    }

    /**
     * @test
     */
    public function rethrowsExceptionsThrownFromTasks()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test');
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::throwException(new Exception('test')));

        $queue = new Queue();
        $queue->enqueue($mock);
    }
}
