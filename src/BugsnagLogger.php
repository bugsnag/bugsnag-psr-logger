<?php

namespace Bugsnag\PsrLogger;

use Bugsnag\Client;
use Bugsnag\Report;
use Exception;
use Psr\Log\LogLevel;
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
     * The minimum level required to notify bugsnag.
     *
     * Logs underneath this level will be converted into breadcrumbs.
     *
     * @var string
     */
    protected $notifyLevel = LogLevel::NOTICE;

    /**
     * The level for the current log record.
     *
     * @var string|null
     */
    protected $level;

    /**
     * The message for the current log record.
     *
     * @var string|\Throwable
     */
    private $message;

    /**
     * The context for the current log record.
     *
     * @var mixed[]
     */
    private $context;

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
     * Set the notifyLevel of the logger, as defined in Psr\Log\LogLevel.
     *
     * @param string $notifyLevel
     *
     * @return void
     */
    public function setNotifyLevel($notifyLevel)
    {
        if (!in_array($notifyLevel, $this->getLogLevelOrder())) {
            syslog(LOG_WARNING, 'Bugsnag Warning: Invalid notify level supplied to Bugsnag Logger');
        } else {
            $this->notifyLevel = $notifyLevel;
        }
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
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;

        $title = $this->extractTitle();
        $exception = $this->extractException();

        // Below threshold, leave a breadcrumb and don't send a notification
        if (!$this->aboveLevel($level, $this->notifyLevel)) {
            $this->leaveBreadcrumb($exception, $title);

            return;
        }

        $report = $this->buildReport($exception, $title);

        $this->client->notify($report);
    }

    /**
     * @return string
     */
    protected function extractTitle()
    {
        $title = 'Log '.$this->level;
        if (isset($context['title'])) {
            $title = $this->context['title'];
            unset($this->context['title']);
        }

        return $title;
    }

    /**
     * @return \Throwable|null
     */
    protected function extractException()
    {
        $exception = null;
        if (isset($this->context['exception']) && ($this->context['exception'] instanceof Exception || $this->context['exception'] instanceof Throwable)) {
            $exception = $this->context['exception'];
            unset($this->context['exception']);
        } elseif ($this->message instanceof Exception || $this->message instanceof Throwable) {
            $exception = $this->message;
        }

        return $exception;
    }

    /**
     * Checks whether the selected level is above another level.
     *
     * @param string $level
     * @param string $base
     *
     * @return bool
     */
    protected function aboveLevel($level, $base)
    {
        $levelOrder = $this->getLogLevelOrder();
        $baseIndex = array_search($base, $levelOrder);
        $levelIndex = array_search($level, $levelOrder);

        return $levelIndex >= $baseIndex;
    }

    /**
     * Returns the log levels in order.
     *
     * @return string[]
     */
    protected function getLogLevelOrder()
    {
        return [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];
    }

    /**
     * @param \Throwable|null $exception
     *
     * @return void
     */
    protected function leaveBreadcrumb($exception, $title)
    {
        if ($exception !== null) {
            $title = get_class($exception);
            $data = ['name' => $title, 'message' => $exception->getMessage()];
        } else {
            $data = ['message' => $this->message];
        }

        $metaData = array_merge($data, $this->context);

        $this->client->leaveBreadcrumb($title, 'log', array_filter($metaData));
    }

    /**
     * @return Report
     */
    protected function buildReport($exception, $title)
    {
        if ($exception !== null) {
            $report = Report::fromPHPThrowable($this->client->getConfig(), $exception);
        } else {
            $report = Report::fromNamedError($this->client->getConfig(), $title, $this->formatMessage($this->message));
        }

        $report->setMetaData($this->context);
        $report->setSeverity($this->getSeverity($this->level));
        $report->setSeverityReason([
            'type' => 'log',
            'attributes' => [
                'level' => $this->level,
            ],
        ]);

        return $report;
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
        if (!$this->aboveLevel($level, 'notice')) {
            return 'info';
        } elseif (!$this->aboveLevel($level, 'warning')) {
            return 'warning';
        } else {
            return 'error';
        }
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
