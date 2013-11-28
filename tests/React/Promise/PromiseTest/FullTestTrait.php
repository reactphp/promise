<?php

namespace React\Promise\PromiseTest;

trait FullTestTrait
{
    use PromiseTestTrait,
        PromiseFulfilledTestTrait,
        PromiseRejectedTestTrait,
        ResolveTestTrait,
        RejectTestTrait,
        ProgressTestTrait;
}
