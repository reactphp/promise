<?php

namespace React\Promise;

use RuntimeException;

class ArrayAccessibleException extends RuntimeException implements \ArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $offset;
    }

    public function offsetSet(mixed $offset, mixed $value): void {}

    public function offsetUnset(mixed $offset): void {}
}
