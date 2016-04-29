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
        for ($i = key($this->queue); isset($this->queue[$i]); $i++) {
            $task = $this->queue[$i];

            $exception = null;

            try {
                $task();
            } catch (\Throwable $exception) {
            } catch (\Exception $exception) {
            }

            unset($this->queue[$i]);

            if ($exception) {
                throw $exception;
            }
        }

        $this->queue = [];
    }
}
