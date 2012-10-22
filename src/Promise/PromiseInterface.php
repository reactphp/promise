<?php

namespace Promise;

/**
 * A Promise represents the pending result of a computation that may not have
 * completed yet.
 * While a Deferred represents the computation itself, a Promise represents
 * the result of that computation. Thus, each Deferred has a Promise that acts
 * as a placeholder for its actual result.
 */
interface PromiseInterface
{
    /**
     * Registers new fulfilled, error and progress handlers with this Promise.
     * All parameters are optional.
     *
     * As per the Promises/A spec, returns a new Promise that will be resolved
     * with the result of $fulfilledHandler if Promise is fulfilled, or with
     * the result of $errorHandler if Promise is rejected.
     *
     * A Promise starts in an unresolved state.
     * At some point the computation will either complete successfully, thus
     * producing a result, or fail, either generating some sort of error why it
     * could not complete.
     *
     * If the computation completes successfully, the Promise will transition
     * to the resolved state and the $fulfilledHandler will be invoked and
     * passed the result.
     *
     * If the computation fails, the Promise will transition to the rejected
     * state and $errorHandler will be invoked and passed the error.
     *
     * The producer of this Promise may trigger progress notifications to
     * indicate that the computation is making progress toward its result.
     * For each progress notification, $progressHandler will be invoked and
     * passed a single parameter (whatever it wants) to indicate progress.
     *
     * Once in the resolved or rejected state, a Promise becomes immutable.
     * Neither its state nor its result (or error) can be modified.
     *
     * A Promise makes the following guarantees about handlers registered in
     * the same call to then():
     *
     *   1. Only one of $fulfilledHandler or $errorHandler will be called,
     *      never both.
     *   2. $fulfilledHandler and $errorHandler will never be called more
     *      than once.
     *   3. $progressHandler may be called multiple times.
     *
     * @param callable $fulfilledHandler
     * @param callable $errorHandler
     * @param callable $progressHandler
     *
     * @return PromiseInterface
     */
    public function then($fulfilledHandler = null, $errorHandler = null, $progressHandler = null);
}
