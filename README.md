Promise
=======

A simple and lightweight implementation of [CommonJS Promises/A](http://wiki.commonjs.org/wiki/Promises/A) for PHP.

If you never heard about Promises before, [read this first](https://gist.github.com/3889970).

Example
-------

``` php
<?php

function getRedisClient() {
    $deferred = new Promise\Deferred();

    $client = new Predis\Async\Client('tcp://127.0.0.1:6379');

    $client->connect(function ($client) use ($deferred) {
        $deferred->resolve($client);
    });

    // Return only the promise, so that the caller cannot
    // resolve, reject, or otherwise muck with the original deferred.
    return $deferred->promise();
}

getRedisClient()
    ->then(function($client) {
        // Do something with $client
    });
```


Credits
-------

Promise is a port of [when.js](https://github.com/cujojs/when) by [Brian Cavalier](https://github.com/briancavalier).

License
-------

Released under the [MIT](https://github.com/jsor/promise/blob/master/LICENSE) license.
