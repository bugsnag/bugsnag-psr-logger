<?php

namespace Bugsnag\PsrLogger\Tests;

use Bugsnag\Client;
use Bugsnag\PsrLogger\BugsnagLogger;
use Exception;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ReportStub
{
    const LOG_LEVEL = 'log_level';
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BugsnagLoggerTest extends TestCase
{
    use MockeryTrait;

    public function testError()
    {
        $exception = new Exception();

        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromPHPThrowable')
            ->with('config', $exception, false, ['type' => 'log', 'attributes' => ['level' => 'error']])
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->log('error', $exception);
    }

    public function testDebug()
    {
        $logger = new BugsnagLogger($client = Mockery::mock(Client::class));

        $client->shouldNotReceive('notify');
        $client->shouldReceive('leaveBreadcrumb')->once();

        $logger->log('debug', 'hi', ['foo' => 'bar']);
    }

    public function testAlert()
    {
        $exception = new Exception();
        
        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromNamedError')
            ->with('config', Mockery::any(), Mockery::any(), false, ['type' => 'log', 'attributes' => ['level' => 'alert']])
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
