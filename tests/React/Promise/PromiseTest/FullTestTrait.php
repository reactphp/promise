<?php

namespace React\Promise\PromiseTest;

trait FullTestTrait
{
    use PromiseTestTrait,
        PromiseFullfilledTestTrait,
        PromiseRejectedTestTrait,
        ResolveTestTrait,
        RejectTestTrait,
        ProgressTestTrait;
}
