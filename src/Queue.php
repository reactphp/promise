<?php

namespace React\Promise;

use React\Promise\Queue\DriverInterface;

final class Queue
{
    /**
     * @var DriverInterface
     */
    private static $driver;

    public static function setDriver(DriverInterface $driver = null)
    {
        self::$driver = $driver;
    }

    public static function getDriver()
    {
        if (!self::$driver) {
            self::$driver = new Queue\SynchronousDriver();
        }

        return self::$driver;
    }

    public static function enqueue(callable $task)
    {
        if (!self::$driver) {
            self::getDriver();
        }

        self::$driver->enqueue($task);
    }
}
