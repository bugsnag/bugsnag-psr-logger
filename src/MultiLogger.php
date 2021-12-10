<?php

namespace Bugsnag\PsrLogger;

use Psr\Log\AbstractLogger;

class MultiLogger extends AbstractLogger
{
    /**
     * The registered loggers.
     *
     * @var \Psr\Log\LoggerInterface[]
     */
    protected $loggers;

    /**
     * Create a new multi logger instance.
     *
     * @param \Psr\Log\LoggerInterface[] $loggers
     *
     * @return void
     */
    public function __construct(array $loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param mixed  $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
