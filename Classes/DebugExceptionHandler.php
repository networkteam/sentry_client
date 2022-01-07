<?php

namespace Networkteam\SentryClient;

class DebugExceptionHandler extends \TYPO3\CMS\Core\Error\DebugExceptionHandler
{
    public function __construct()
    {
        parent::__construct();
        Client::init();
    }

    /**
     * @param \Throwable $exception The throwable object.
     * @throws \Exception
     */
    public function handleException(\Throwable $exception): void
    {
        Client::captureException($exception);
        parent::handleException($exception);
    }
}