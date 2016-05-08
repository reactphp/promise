CHANGELOG for 3.x
=================

* 3.0.0 (xxxx-xx-xx)

    New major release.

    * Introduce a global task queue to eliminate recursion and reduce stack size
      when chaining promises.
    * BC break: The progression API has been removed (#32).
    * BC break: The promise-array related functions (`all()`, `race()`, `any()`,
      `some()`, `map()`, `reduce()`) now require an array of promises or values
      as input. Before, arrays and promises which resolve to an array were
      supported, other input types resolved to empty arrays or `null`. (#35).
