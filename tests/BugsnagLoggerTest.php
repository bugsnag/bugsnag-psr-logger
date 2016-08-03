<?php

namespace Bugsnag\PsrLogger\Tests;

use Bugsnag\Client;
use Bugsnag\PsrLogger\BugsnagLogger;
use Exception;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class BugsnagLoggerTest extends TestCase
{
    use MockeryTrait;

    public function testError()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldReceive('notifyException')->once();
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger->log('error', new Exception());
    }

    public function testDebug()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldNotReceive('notifyException');
        $client->shouldReceive('leaveBreadcrumb')->once();

        $logger->log('debug', 'hi', ['foo' => 'bar']);
    }

    public function testAlert()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldReceive('notifyError')->once();
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
