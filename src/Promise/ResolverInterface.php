<?php

namespace Promise;

interface ResolverInterface
{
    public function resolve($result = null);

    public function reject($error = null);

    public function progress($update = null);
}
