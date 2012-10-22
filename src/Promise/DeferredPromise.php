<?php

namespace Promise;

/**
 * A Promise bound to a Deferred.
 *
 * The state is unknown at the time of creation and will be defined by the
 * producer of the Deferred by calling resolve/reject methods of the Deferred.
 */
class DeferredPromise implements PromiseInterface
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
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null)
    {
        return $this->deferred->then($fulfilledHandler, $errorHandler, $progressHandler);
    }
}
