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

    public function testContextExceptionOverride()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $exception = new Exception();
        $client->shouldReceive('notifyException')
            ->once()
            ->withArgs([$exception, \Mockery::any()]);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger->log('error', 'terrible things!', ['exception' => $exception]);
    }

    public function testInfo()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldNotReceive('notifyException');
        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log info', 'log', ['foo' => 'bar', 'message' => 'hi']]);

        $logger->log('info', 'hi', ['foo' => 'bar']);
    }

    public function testDebug()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldNotReceive('notifyException');
        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log debug', 'log', ['foo' => 'bar', 'message' => 'hi']]);

        $logger->log('debug', 'hi', ['foo' => 'bar']);
    }

    public function testAlert()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldReceive('notifyError')
            ->once()
            ->withArgs(['Log alert', 'hi!', \Mockery::any()]);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
