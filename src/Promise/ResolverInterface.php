<?php

namespace Promise;

/**
 * A Resolver can resolve, reject or trigger progress notification on behalf of
 * a Deferred without knowing any details about consumers.
 *
 * Sometimes it can be useful to hand out a resolver and allow another
 * (possibly untrusted) party to provide the resolution value for a promise.
 */
interface ResolverInterface
{
    /**
     * Resolve a Deferred.
     *
     * All consumers are notified by having their $fulfilledHandler (which they
     * registered via then()) called with the result.
     *
     * @param  mixed   $result
     * @return Promise
     */
    public function resolve($result = null);

    /**
     * Reject a Deferred, signalling that the Deferred's computation failed.
     *
     * All consumers are notified by having their $errorHandler (which they
     * registered via then()) called with the error.
     *
     * @param  mixed   $error
     * @return Promise
     */
    public function reject($error = null);

    /**
     * Trigger progress notifications, to indicate to consumers that the
     * computation is making progress toward its result.
     *
     * All consumers are notified by having their $progressHandler (which they
     * registered via then()) called with the update.
     *
     * @param mixed $update
     */
    public function progress($update = null);
}
