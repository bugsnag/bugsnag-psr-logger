<?php

namespace Bugsnag\PsrLogger\Tests;

use Bugsnag\Client;
use Bugsnag\Configuration;
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

        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;

        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromPHPThrowable')
            ->with($config, $exception)
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'error']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->log('error', $exception);
    }

    public function testContextExceptionOverride()
    {
        $exception = new Exception();
        
        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;

        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromPHPThrowable')
            ->with($config, $exception)
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'error']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');
        
        $logger = new BugsnagLogger($client);
        $logger->log('error', 'terrible things!', ['exception' => $exception]);
    }

    public function testContextExceptionInvalidOverride()
    {
        $exception = new Exception();

        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;

        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromNamedError')
            ->with($config, Mockery::any(), Mockery::any())
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'error']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldNotReceive('leaveBreadcrumb');
        $client->shouldReceive('notify')->once()->with($report);

        $logger = new BugsnagLogger($client);
        $logger->log('error', 'terrible things!', ['exception' => 'not an exception']);
    }

    public function testInfo()
    {
        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldNotReceive('notifyException');
        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log info', 'log', ['foo' => 'bar', 'message' => 'hi']]);

        $logger = new BugsnagLogger($client);
        $logger->log('info', 'hi', ['foo' => 'bar']);
    }

    public function testDebug()
    {
        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldNotReceive('notifyException');
        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log debug', 'log', ['foo' => 'bar', 'message' => 'hi']]);

        $logger = new BugsnagLogger($client);
        $logger->log('debug', 'hi', ['foo' => 'bar']);
    }

    public function testAlert()
    {
        $exception = new Exception();

        $config = Mockery::mock(Configuration::class);
        $config->logThreshold = null;
        $config->logWarningLevel = null;
        $config->logErrorLevel = null;
        
        $report = Mockery::namedMock('Bugsnag\Report', ReportStub::class);
        $report->shouldReceive('fromNamedError')
            ->with($config, Mockery::any(), Mockery::any())
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setMetaData')->once()->with(Mockery::any());
        $report->shouldReceive('setSeverity')->once()->with('error');
        $report->shouldReceive('setSeverityReason')->once()->with(['type' => 'log', 'attributes' => ['level' => 'alert']]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldReceive('notify')->once()->with($report);
        $client->shouldNotReceive('leaveBreadcrumb');

        $logger = new BugsnagLogger($client);
        $logger->alert('hi!', ['foo' => 'baz']);
    }
}
