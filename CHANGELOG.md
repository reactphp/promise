CHANGELOG
=========


* 1.0.2 (2012-11-14)

  * Fix bug in When::any() not correctly unwrapping to a single result value
  * $promiseOrValue argument of When::resolve() and When::reject() is now optional

* 1.0.1 (2012-11-13)

  * Prevent deep recursion which was reaching `xdebug.max_nesting_level` default of 100

* 1.0.0 (2012-11-07)

  * First tagged release
