Changelog
=========

## TBD

### Deprecations

* Deprecated `Bugsnag\PsrLogger\AbstractLogger` in favour of using `Psr\Log\AbstractLogger` directly. `Bugsnag\PsrLogger\AbstractLogger` will be removed in the next major version
  [#49](https://github.com/bugsnag/bugsnag-psr-logger/pull/49)

## 1.4.4 (2021-11-18)

* Added support for PSR Log v2
  [#46](https://github.com/bugsnag/bugsnag-psr-logger/pull/46)

## 1.4.3 (2020-02-26)

### Bug fixes

* Added support for PHP 7.2, 7.3 and 7.4
  [#38](https://github.com/bugsnag/bugsnag-psr-logger/pull/38)

## 1.4.2 (2019-08-28)

### Bug fixes

* Fix warnings being set with error severity in event
  (fixes [#35](https://github.com/bugsnag/bugsnag-psr-logger/issues/35))
  [#37](https://github.com/bugsnag/bugsnag-psr-logger/pull/37)

## 1.4.1 (2018-02-16)

### Bug fixes

* Unset exception context before forwarding metadata to the error report. This
  removes the extraneous 'Exception' tab from error reports.
  [#30](https://github.com/bugsnag/bugsnag-psr-logger/pull/30)
  [Graham Campbell](https://github.com/GrahamCampbell)

## 1.4.0 (2017-12-21)

### Enhancements

* Bumped Bugsnag-PHP version to 3.10 to enable `addMetaData` functionality

### Fixes

* Fixed issue where not all log-levels are present, preventing warning notifications
  [#29](https://github.com/bugsnag/bugsnag-psr-logger/pull/29)

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
