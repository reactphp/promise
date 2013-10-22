<?php

namespace React\Promise;

class Deferred implements PromisorInterface
{
    private $completed;
    private $promise;
    private $handlers = [];
    private $progressHandlers = [];

    public function resolve($value = null)
    {
        if (null !== $this->completed) {
            return resolve($value);
        }

        $this->completed = resolve($value);

        $this->processQueue($this->handlers, $this->completed);

        $this->progressHandlers = $this->handlers = [];

        return $this->completed;
    }

    public function reject($reason = null)
    {
        return $this->resolve(reject($reason));
    }

    public function progress($update = null)
    {
        if (null !== $this->completed) {
            return;
        }

        $this->processQueue($this->progressHandlers, $update);
    }

    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new DeferredPromise($this->getThenCallback());
        }

        return $this->promise;
    }

    protected function getThenCallback()
    {
        return function (callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null) {
            if (null !== $this->completed) {
                return $this->completed->then($onFulfilled, $onRejected, $onProgress);
            }

            $deferred = new static();

            if (is_callable($onProgress)) {
                $progHandler = function ($update) use ($deferred, $onProgress) {
                    try {
                        $deferred->progress(call_user_func($onProgress, $update));
                    } catch (\Exception $e) {
                        $deferred->progress($e);
                    }
                };
            } else {
                $progHandler = [$deferred, 'progress'];
            }

            $this->handlers[] = function ($promise) use ($onFulfilled, $onRejected, $deferred, $progHandler) {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->then(
                        [$deferred, 'resolve'],
                        [$deferred, 'reject'],
                        $progHandler
                    );
            };

            $this->progressHandlers[] = $progHandler;

            return $deferred->promise();
        };
    }

    protected function processQueue($queue, $value)
    {
        foreach ($queue as $handler) {
            call_user_func($handler, $value);
        }
    }
}
