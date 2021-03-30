<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ConfigurationService;
use Sentry\EventId;

class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler
{
    /**
     * @var EventId
     */
    protected $eventId;

    /**
     * @param \Throwable $exception The throwable object.
     * @throws \Exception
     */
    public function handleException(\Throwable $exception): void
    {
        $this->eventId = Client::captureException($exception);
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
}