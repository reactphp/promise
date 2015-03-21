<?php

namespace React\Promise;

interface QueueInterface
{
    public function enqueue(callable $task);
}
