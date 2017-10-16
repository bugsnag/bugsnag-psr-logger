# Bugsnag PHP PSR Logger

[![Build Status](https://img.shields.io/travis/bugsnag/bugsnag-psr-logger/master.svg?style=flat-square)](https://travis-ci.org/bugsnag/bugsnag-psr-logger)
[![StyleCI Status](https://styleci.io/repos/62041635/shield?branch=master)](https://styleci.io/repos/62041635)


The Bugsnag PHP PSR logger is an implementation of the [Fig PSR logging standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) that provides a standard interface to logging to Bugsnag.


## Getting Started


### Installing

Add `bugsnag/bugsnag-psr-logger` to your `composer.json`.  

### Configuring

This library provides a logger interface but uses the [bugsnag-php notifier library](https://github.com/bugsnag/bugsnag-php) as a base.  All configuration should be performed as described in the [official bugsnag-php documentation](https://docs.bugsnag.com/platforms/php/).

### Using the Loggers

The library provides two loggers, `BugsnagLogger` and `MultiLogger`.

`BugsnagLogger` will automatically send a notification to Bugsnag if it receives a message with a severity higher than `info`.  This will allow you to notify of any handled exceptions through interfacing the logger directly with the framework you are using.  Ensure that the logger can communicate with the `bugsnag-php` library by passing the `client` object into it on creation.

```php
$bugsnag = Bugsnag\Client::make('YOUR-API-KEY-HERE');
$logger = new Bugsnag\PsrLogger\BugsnagLogger($bugsnag);

# Will send a notification to bugsnag
$logger.error('An error occurred');
```


If you wish to use a separate logger alongside `BugsnagLogger` you will need to use `MutliLogger`.  By passing it an array of `Logger` objects on construction, `MultiLogger` will call into each passed `Logger` in turn when a message is logged.

```php
$logger = new Bugsnag\PsrLogger\BugsnagLogger($bugsnag);
$mySecondLogger = new Logger();
$multiLogger = new Bugsnag\PsrLogger\MultiLogger([$logger, $mySecondLogger]);

# Will log to $mySecondLogger and send a notification to bugsnag through $logger
$mutliLogger.error('An error occurred');
```


For more information on integrating the loggers into specific frameworks see the individual setup information found in the [bugsnag-php documentation](https://docs.bugsnag.com/platforms/php/).


## Contributing

All contributors are welcome! For information on how to build, test and release
`bugsnag-psr-logger`, see our [contributing guide](CONTRIBUTING.md). Feel free
to comment on [existing issues](https://github.com/bugsnag/bugsnag-psr-logger/issues)
for clarification or starting points.

## License

The Bugsnag PSR logger is free software released under the MIT License.
See [LICENSE.txt](LICENSE.txt) for details.
