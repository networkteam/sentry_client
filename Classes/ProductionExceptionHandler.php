<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Event\BeforeSentryCaptureEvent;
use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Service\SentryService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sentry\EventId;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler
{
    protected ?EventId $eventId = null;

    public function __construct()
    {
        parent::__construct();
        SentryService::inititalize();
    }

    /**
     * @param \Throwable $exception The throwable object.
     * @throws \Exception
     */
    public function handleException(\Throwable $exception): void
    {
        $ignoredCodes = array_merge(self::IGNORED_EXCEPTION_CODES, self::IGNORED_HMAC_EXCEPTION_CODES);

        if (!in_array($exception->getCode(), $ignoredCodes, true)) {
            $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);

            $event = $eventDispatcher->dispatch(
                new BeforeSentryCaptureEvent($exception)
            );

            if (!$event->isPropagationStopped()) {
                $exceptionToSend = $event->getException();
                $this->eventId = Client::captureException($exceptionToSend);
            }
        }

        parent::handleException($exception);
    }

    /**
     * Returns the title for the error message
     *
     * @param \Throwable $exception The throwable object.
     * @return string
     */
    protected function getTitle(\Throwable $exception): string
    {
        if (ConfigurationService::showEventId()) {
            return sprintf('%s Event: %s', parent::getTitle($exception), $this->eventId);
        }
        return parent::getTitle($exception);
    }

    /**
     * Writes an exception in the sys_log table
     */
    protected function writeLog(string $logMessage)
    {
        if (SentryService::isEnabled() && ConfigurationService::shouldDisableDatabaseLogging()) {
            return;
        }

        parent::writeLog($logMessage);
    }
}
