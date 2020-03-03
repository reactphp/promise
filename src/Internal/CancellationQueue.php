<?php

namespace React\Promise\Internal;

/**
 * @internal
 */
final class CancellationQueue
{
    private $started = false;
    private $queue = [];

    public function __invoke(): void
    {
        if ($this->started) {
            return;
        }

        $this->started = true;
        $this->drain();
    }

    public function enqueue($cancellable): void
    {
        if (!\is_object($cancellable) || !\method_exists($cancellable, 'then') || !\method_exists($cancellable, 'cancel')) {
            return;
        }

        $length = \array_push($this->queue, $cancellable);

        if ($this->started && 1 === $length) {
            $this->drain();
        }
    }

    private function drain(): void
    {
        for ($i = \key($this->queue); isset($this->queue[$i]); $i++) {
            $cancellable = $this->queue[$i];

            $exception = null;

            try {
                $cancellable->cancel();
            } catch (\Throwable $exception) {
            }

            unset($this->queue[$i]);

            if ($exception) {
                throw $exception;
            }
        }

        $this->queue = [];
    }
}
