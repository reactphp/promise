<?php

namespace React\Promise\Queue;

interface DriverInterface
{
    public function enqueue(callable $task);
}
