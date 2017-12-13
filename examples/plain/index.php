<?php

require_once 'vendor/autoload.php';

$bugsnag = Bugsnag\Client::make('YOUR-API-KEY');
$logger = new Bugsnag\PsrLogger\BugsnagLogger($bugsnag);
$logger->setNotifyLevel(\Psr\Log\LogLevel::ERROR);

// Add breadcrumbs for low-severity log messages
$logger->notice('Reticulating splines');

// Log an exception to Bugsnag
$logger->error(new Exception('Invalid configuration at runtime'));
