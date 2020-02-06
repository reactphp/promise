<?php

namespace React\Promise\Internal;

/**
 * @internal
 */
final class Queue
{
    private $queue = [];

    public function enqueue(callable $task): void
    {
        if (1 === \array_push($this->queue, $task)) {
            $this->drain();
        }
    }

    private function drain(): void
    {
        for ($i = \key($this->queue); isset($this->queue[$i]); $i++) {
            try {
                ($this->queue[$i])();
            } finally {
                unset($this->queue[$i]);
            }
        }

        $this->queue = [];
    }
}
