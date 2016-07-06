<?php

namespace Bugsnag\PsrLogger\Tests;

use Bugsnag\Client;
use Bugsnag\PsrLogger\BugsnagLogger;
use Exception;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Mockery;

class BugsnagLoggerTest extends TestCase
{
    use MockeryTrait;

    public function testCanLog()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldReceive('notifyException')->once();

        $logger->log('error', new Exception());
    }

    public function testCanSkip()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldNotReceive('notifyException');

        $logger->log('debug', 'hi', ['foo' => 'bar']);
    }

    public function testAlert()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldReceive('notifyError')->once();

        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
