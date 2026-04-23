<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Event\BeforeSentryCaptureEvent;
use Networkteam\SentryClient\Service\SentryService;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DebugExceptionHandler extends \TYPO3\CMS\Core\Error\DebugExceptionHandler
{
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
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);

        $event = $eventDispatcher->dispatch(
            new BeforeSentryCaptureEvent($exception)
        );

        if (!$event->isPropagationStopped()) {
            $exceptionToSend = $event->getException();
            Client::captureException($exceptionToSend);
        }

        parent::handleException($exception);
    }
}
