<?php

if (!class_exists(Fiber::class)) {
    /**
     * Fiber stub to make PHPStan happy on PHP < 8.1
     *
     * @link https://www.php.net/manual/en/class.fiber.php
     * @copyright Copyright (c) 2023 Christian Lück, taken from https://github.com/clue/framework-x with permission
     */
    class Fiber
    {
        public static function suspend(mixed $value): void
        {
            // NOOP
        }

        public function __construct(callable $callback)
        {
            assert(is_callable($callback));
        }

        public function start(): int
        {
            return 42;
        }
    }
}
