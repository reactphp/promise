<?php

namespace Promise\Tests\Stub;

class FakeRejectedPromise
{
    private $val;

    public function __construct($val = null)
    {
        $this->val = $val;
    }

    public function then($callback = null, $errback = null)
    {
        return $errback ? new FakeResolvedPromise(call_user_func($errback, $this->val)) : new self($this->val);
    }
}
