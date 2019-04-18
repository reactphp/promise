CHANGELOG for 3.x
=================

* 3.0.0 (xxxx-xx-xx)

    New major release.

    * Introduce a global task queue to eliminate recursion and reduce stack size
      when chaining promises (#28).
    * BC break: The progression API has been removed (#32).
    * BC break: The promise-array related functions (`all()`, `race()`, `any()`,
      `some()`, `map()`, `reduce()`) now require an array of promises or values
      as input. Before, arrays and promises which resolve to an array were
      supported, other input types resolved to empty arrays or `null` (#35).
    * BC break: `race()` now returns a forever pending promise when called with
      an empty array (#83).
      The behavior is now also in line with the ES6 specification.
    * BC break: The interfaces `PromiseInterface`, `ExtendedPromiseInterface`
      and `CancellablePromiseInterface` have been merged into a single
      `PromiseInterface` (#75).

      Please note, that the following code (which has been commonly used to
      conditionally cancel a promise) is not longer possible:

      ```php
      if ($promise instanceof CancellablePromiseInterface) {
          $promise->cancel();
      }
      ```

      If only supporting react/promise >= 3.0, it can be simply changed to:

      ```php
      if ($promise instanceof PromiseInterface) {
          $promise->cancel();
      }
      ```

      If also react/promise < 3.0 must be supported, the following code can be
      used:

      ```php
      if ($promise instanceof PromiseInterface) {
          \React\Promise\resolve($promise)->cancel();
      }
      ```
    * BC break: When rejecting a promise, a `\Throwable` or `\Exception`
      instance is enforced as the rejection reason (#93).
      This means, it is not longer possible to reject a promise without a reason
      or with another promise.
