<?php

namespace Bugsnag\PsrLogger;

use GrahamCampbell\TestBenchCore\MockeryTrait;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MultiLoggerTest extends TestCase
{
    use MockeryTrait;

    public function testCanLog()
    {
        $one = Mockery::mock(LoggerInterface::class);
        $one->shouldReceive('log')->once()->with('info', 'hi', ['foo' => 'bar']);
        $two = Mockery::mock(LoggerInterface::class);
        $two->shouldReceive('log')->once()->with('info', 'hi', ['foo' => 'bar']);

        $multi = new MultiLogger([$one, $two]);

        $multi->log('info', 'hi', ['foo' => 'bar']);
    }

    public function testWarning()
    {
        $one = Mockery::mock(LoggerInterface::class);
        $one->shouldReceive('log')->once()->with('warning', 'hi!', ['foo' => 'baz']);
        $two = Mockery::mock(LoggerInterface::class);
        $two->shouldReceive('log')->once()->with('warning', 'hi!', ['foo' => 'baz']);

        $multi = new MultiLogger([$one, $two]);

        $multi->warning('hi!', ['foo' => 'baz']);
    }

    public function testIsLoggerInterface()
    {
        $one = Mockery::mock(LoggerInterface::class);
        $logger = new MultiLogger([$one]);

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
