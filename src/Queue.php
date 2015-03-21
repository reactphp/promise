<?php

namespace React\Promise;

class Queue implements QueueInterface
{
    private $queue = [];
    private $resumeAt = null;

    public function enqueue(callable $task)
    {
        $length = array_push($this->queue, $task);

        if (null === $this->resumeAt && 1 !== $length) {
            return;
        }

        $this->drain();
    }

    private function drain()
    {
        $start = null !== $this->resumeAt ? $this->resumeAt : 0;

        for ($i = $start; isset($this->queue[$i]); $i++) {
            $task = $this->queue[$i];

            try {
                $task();
            } catch (\Exception $e) {
                if (isset($this->queue[$i + 1])) {
                    $this->resumeAt = $i + 1;
                } else {
                    $this->resumeAt = null;
                    $this->queue = [];
                }

                throw $e;
            }
        }

        $this->resumeAt = null;
        $this->queue = [];
    }
}
