<?php

namespace React\Promise\Queue;

interface QueueInterface
{
    public function enqueue(callable $task);
}
