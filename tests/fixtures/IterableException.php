<?php

namespace React\Promise;

class IterableException extends \RuntimeException implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }
}
