<?php

namespace Networkteam\SentryClient;

class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler
{
    /**
     * @param \Throwable $exception The throwable object.
     * @throws \Exception
     */
    public function handleException(\Throwable $exception)
    {
        Client::captureException($exception);
        parent::handleException($exception);
    }
}