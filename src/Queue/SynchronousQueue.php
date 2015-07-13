<?php

namespace React\Promise\Queue;

class SynchronousQueue implements QueueInterface
{
    private $queue = [];

    public function enqueue(callable $task)
    {
        if (1 === array_push($this->queue, $task)) {
            $this->drain();
        }
    }

    private function drain()
    {
        /** @var callable $task */
        while ($task = array_shift($this->queue)) {
            $task();
        }
    }
}
