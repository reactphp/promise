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
     * @requires PHP 8.1
     */
    public function executesFollowingTasksIfPriorTaskSuspendsFiber()
    {
        $queue = new Queue();

        $fiber = new \Fiber(function () use ($queue) {
            $queue->enqueue(function () {
                \Fiber::suspend(2);
            });
            return 1;
        });

        $ret = $fiber->start();
        $this->assertEquals(2, $ret);

        $queue->enqueue($this->expectCallableOnce());
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
