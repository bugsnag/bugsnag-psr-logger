<?php

namespace Bugsnag\PsrLogger;

use Bugsnag\Client;
use Bugsnag\Report;
use Exception;
use Throwable;

class BugsnagLogger extends AbstractLogger
{
    /**
     * The bugsnag client instance.
     *
     * @var \Bugsnag\Client
     */
    protected $client;

    /**
     * Create a new bugsnag logger instance.
     *
     * @param \Bugsnag\Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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
    public function log($level, $message, array $context = [])
    {
        $title = 'Log ' . $level;
        if (isset($context['title'])) {
            $title = $context['title'];
            unset($context['title']);
        }

        $exception = null;
        if (isset($context['exception']) && ($context['exception'] instanceof Exception || $context['exception'] instanceof Throwable)) {
            $exception = $context['exception'];
        } else if ($message instanceof Exception || $message instanceof Throwable) {
            $exception = $message;
        }

        if ($level === 'debug' || $level === 'info') {
            if ($exception !== null) {
                $title = get_class($exception);
                $data = ['name' => $title, 'message' => $exception->getMessage()];
            } else {
                $data = ['message' => $message];
            }

            $metaData = array_merge($data, $context);

            $this->client->leaveBreadcrumb($title, 'log', array_filter($metaData));

            return;
        }

        $severityReason = [
            'type' => 'log',
            'attributes' => [
                'level' => $level
            ]
        ];

        if ($exception !== null) {
            $report = Report::fromPHPThrowable($this->client->getConfig(), $exception);
        } else {
            $report = Report::fromNamedError($this->client->getConfig(), $title, $this->formatMessage($message));
        }

        $report->setMetaData($context);
        $report->setSeverity($this->getSeverity($level));
        $report->setSeverityReason($severityReason);

        $this->client->notify($report);
    }

    /**
     * Get the severity for the logger.
     *
     * @param string $level
     *
     * @return string
     */
    protected function getSeverity($level)
    {
        if ($level === 'notice') {
            return 'info';
        }

        if ($level === 'warning') {
            return 'warning';
        }

        return 'error';
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        }

        return $message;
    }

    /**
     * Ensure the given string is less than 100 characters.
     *
     * @param string $str
     *
     * @return string
     */
    protected function limit($str)
    {
        if (strlen($str) <= 100) {
            return $str;
        }

        return rtrim(substr($str, 0, 97)).'...';
    }
}
