<?php

namespace Bugsnag\PsrLogger;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\PsrLogger\BugsnagLogger;
use Exception;
use TypeError;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ReportStub
{
    const LOG_LEVEL = 'log_level';
}

global $sysprio;
global $sysmes;

function syslog($priority, $message) {
    global $sysprio;
    global $sysmes;

    $sysprio = $priority;
    $sysmes = $message;
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
        $config->shouldReceive('getLogLevel')->andReturn(null);

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
        $config->shouldReceive('getLogLevel')->andReturn(null);

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
        $config->shouldReceive('getLogLevel')->andReturn(null);

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
        $config->shouldReceive('getLogLevel')->andReturn(null);

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
        $config->shouldReceive('getLogLevel')->andReturn(null);

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
        $config = Mockery::mock(Configuration::class)->makePartial();
        $client = Mockery::mock(Client::class)->makePartial();

        $client->shouldReceive('getConfig')->andReturn($config);
        $client->shouldNotReceive('leaveBreadcrumb');
        $client->shouldReceive('notify')->once()
            ->andReturnUsing(function ($report) {
                $this->assertSameInBlock('error', $report->getSeverity());
                $this->assertSameInBlock('hi!', $report->getMessage());
                $this->assertSameInBlock('Log alert', $report->getName());
                $this->assertSameInBlock('log', $report->getSeverityReason()['type']);
                $this->assertSameInBlock('alert', $report->getSeverityReason()['attributes']['level']);
                $this->assertSameInBlock('baz', $report->getMetaData()['foo']);
            });

        $logger = new BugsnagLogger($client);
        $logger->alert('hi!', ['foo' => 'baz']);
    }

    public function testSetNotifyLevel()
    {
        $config = Mockery::mock(Configuration::class)->makePartial();
        $client = Mockery::mock(Client::class)->makePartial();
        $client->shouldReceive('getConfig')->andReturn($config);

        $logger = new BugsnagLogger($client);
        $logger->setNotifyLevel(\Psr\Log\LogLevel::ERROR);

        $client->shouldReceive('notify')->once()
            ->andReturnUsing(function ($report) {
                $this->assertSameInBlock('error', $report->getSeverity());
                $this->assertSameInBlock('Log alert', $report->getName());
                $this->assertSameInBlock('log', $report->getSeverityReason()['type']);
                $this->assertSameInBlock('alert', $report->getSeverityReason()['attributes']['level']);
                $this->assertSameInBlock('fuu', $report->getMetaData()['bar']);
            });
        $logger->alert('hi', ['bar' => 'fuu']);

        $client->shouldReceive('notify')->once()
            ->andReturnUsing(function ($report) {
                $this->assertSameInBlock('error', $report->getSeverity());
                $this->assertSameInBlock('Log emergency', $report->getName());
                $this->assertSameInBlock('log', $report->getSeverityReason()['type']);
                $this->assertSameInBlock('emergency', $report->getSeverityReason()['attributes']['level']);
                $this->assertSameInBlock('fii', $report->getMetaData()['bar']);
            });
        $logger->emergency('hi', ['bar' => 'fii']);

        $client->shouldReceive('notify')->once()
            ->andReturnUsing(function ($report) {
                $this->assertSameInBlock('error', $report->getSeverity());
                $this->assertSameInBlock('Log critical', $report->getName());
                $this->assertSameInBlock('log', $report->getSeverityReason()['type']);
                $this->assertSameInBlock('critical', $report->getSeverityReason()['attributes']['level']);
                $this->assertSameInBlock('foo', $report->getMetaData()['bar']);
            });
        $logger->critical('hi', ['bar' => 'foo']);

        $client->shouldReceive('notify')->once()
            ->andReturnUsing(function ($report) {
                $this->assertSameInBlock('error', $report->getSeverity());
                $this->assertSameInBlock('Log error', $report->getName());
                $this->assertSameInBlock('log', $report->getSeverityReason()['type']);
                $this->assertSameInBlock('error', $report->getSeverityReason()['attributes']['level']);
                $this->assertSameInBlock('faa', $report->getMetaData()['bar']);
            });
        $logger->error('hi', ['bar' => 'faa']);

        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log warning', 'log', ['foo' => 'baz', 'message' => 'hi']]);
        $logger->warning('hi', ['foo' => 'baz']);

        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log notice', 'log', ['foo' => 'baz', 'message' => 'hi']]);
        $logger->notice('hi', ['foo' => 'baz']);

        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log debug', 'log', ['foo' => 'baz', 'message' => 'hi']]);
        $logger->debug('hi', ['foo' => 'baz']);

        $client->shouldReceive('leaveBreadcrumb')
            ->once()
            ->withArgs(['Log info', 'log', ['foo' => 'baz', 'message' => 'hi']]);
        $logger->info('hi', ['foo' => 'baz']);
    }

    public function testInvalidLogLevelCallsSyslog()
    {
        $config = Mockery::mock(Configuration::class)->makePartial();
        $client = Mockery::mock(Client::class)->makePartial();
        $client->shouldReceive('getConfig')->andReturn($config);

        global $sysprio;
        global $sysmes;

        $sysprio = null;
        $sysmes = null;

        $logger = new BugsnagLogger($client);
        $logger->setNotifyLevel('not a real log level');
        $this->assertEquals($sysprio, LOG_WARNING);
        $this->assertEquals($sysmes, 'Bugsnag Warning: Invalid notify level supplied to Bugsnag Logger');
    }

    /**
     * Makeshift assertion to ensure test context not lost within closures.
     */
    private function assertSameInBlock($expected, $actual)
    {
        if ($expected != $actual) {
            throw new Exception("Expected '".$expected."' received '".$actual."'");
        }
    }
}
