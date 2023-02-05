<?php
declare(strict_types=1);
namespace Networkteam\SentryClient;

use Sentry\EventId;
use Sentry\Severity;
use TYPO3\CMS\Core\Log\LogLevel;
use function Sentry\captureException;
use function Sentry\captureMessage;

class Client
{
    /**
     * Send an exception to Sentry
     */
    public static function captureException(\Throwable $exception): ?EventId
    {
        return captureException($exception);
    }

    /**
     * Send a message to Sentry
     */
    public static function captureMessage(string $message, string $loglevel = 'info'): ?EventId
    {
        return captureMessage($message, self::createSeverity($loglevel));
    }

    protected static function createSeverity(string $loglevel): Severity
    {
        switch ($loglevel) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                $severityValue = Severity::FATAL;
                break;
            case LogLevel::ERROR:
                $severityValue = Severity::ERROR;
                break;
            case LogLevel::WARNING:
                $severityValue = Severity::WARNING;
                break;
            default:
                $severityValue = Severity::INFO;
        }
        return new Severity($severityValue);
    }
}
