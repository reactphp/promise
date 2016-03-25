<?php

namespace React\Promise;

class CancellationQueue
{
    private $started = false;

    /**
     * @var CancellablePromiseInterface[]
     */
    private $queue = [];

    public function __invoke()
    {
        if ($this->started) {
            return;
        }

        $this->started = true;
        $this->drain();
    }

    public function enqueue($promise)
    {
        if (!$promise instanceof CancellablePromiseInterface) {
            return;
        }

        $length = array_push($this->queue, $promise);

        if ($this->started && 1 === $length) {
            $this->drain();
        }
    }

    private function drain()
    {
        for ($i = key($this->queue); isset($this->queue[$i]); $i++) {
            $promise = $this->queue[$i];

            $exception = null;

            try {
                $promise->cancel();
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
