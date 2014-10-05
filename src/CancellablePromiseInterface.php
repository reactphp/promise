<?php

namespace React\Promise;

interface CancellablePromiseInterface
{
    /**
     * @return void
     */
    public function cancel();
}
