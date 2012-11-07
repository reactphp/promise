<?php

namespace React\Promise;

class DeferredResolver implements ResolverInterface
{
    private $deferred;

    public function __construct(Deferred $deferred)
    {
        $this->deferred = $deferred;
    }

    public function resolve($result = null)
    {
        return $this->deferred->resolve($result);
    }

    public function reject($reason = null)
    {
        return $this->deferred->reject($reason);
    }

    public function progress($update = null)
    {
        return $this->deferred->progress($update);
    }
}
