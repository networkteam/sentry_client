<?php
declare(strict_types=1);
namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ExceptionBlacklistService;
Use Networkteam\SentryClient\Service\SentryService;
use Sentry\EventId;
use Sentry\Severity;
use TYPO3\CMS\Core\Log\LogLevel;
use function Sentry\captureException;
use function Sentry\captureMessage;

class Client
{

    public static function captureException(\Throwable $exception): ?EventId
    {
        if (ExceptionBlacklistService::shouldHandleException($exception)) {
            $eventId = captureException($exception);
            return $eventId;
        }

        return null;
    }

    public static function captureMessage(string $message, string $loglevel = 'info'): ?EventId
    {
        if (SentryService::isEnabled()) {
            return captureMessage($message, self::createSeverity($loglevel));
        }

        return null;
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
