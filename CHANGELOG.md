Changelog
=========

## 1.3.0 (2017-12-14)

### Enhancements

* Added log-level configuration option
  [#23](https://github.com/bugsnag/bugsnag-psr-logger/pull/23)

## 1.2.1 (2017-10-06)

### Bug fixes

* Only override message using `$exception` from context if its an instance of
  Exception or Throwable
  [#21](https://github.com/bugsnag/bugsnag-psr-logger/pull/21)
  [Josh Brown](https://github.com/joshbrw)

## 1.2.0 (2017-10-02)

* Added severity data for handled/unhandled feature
  [#19](https://github.com/bugsnag/bugsnag-psr-logger/pull/19)

## 1.1.1 (2017-09-22)

* Support overriding using message parameter as exception by passing an
  exception as context
  [#20](https://github.com/bugsnag/bugsnag-psr-logger/pull/20)

## 1.1.0 (2016-08-08)

* Added support for breadrumbs

## 1.0.1 (2016-07-08)

* Lowered the minimum PHP version to 5.5.0

## 1.0.0 (2016-07-07)

* First public release
