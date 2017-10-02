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
            ->with('config', $exception)
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'error']]);
        
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->log('error', $exception);
    }

    public function testContextExceptionOverride()
    {
        $exception = new Exception();

        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromPHPThrowable')
            ->with('config', $exception)
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'error']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');
        
        $logger = new BugsnagLogger($client);
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
        $exception = new Exception();
        
        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromNamedError')
            ->with('config', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'alert']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
