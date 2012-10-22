<?php

namespace Promise;

/**
 * A Resolver for a Deferred.
 */
class DeferredResolver implements ResolverInterface
{
    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * Constructor
     *
     * @param Deferred $deferred
     */
    public function __construct(Deferred $deferred)
    {
        $this->deferred = $deferred;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($result = null)
    {
        return $this->deferred->resolve($result);
    }

    /**
     * {@inheritDoc}
     */
    public function reject($error = null)
    {
        return $this->deferred->reject($error);
    }

    /**
     * {@inheritDoc}
     */
    public function progress($update = null)
    {
        return $this->deferred->progress($update);
    }
}
