<?php

namespace Networkteam\SentryClient\Content;

use Networkteam\SentryClient\Client;
use Networkteam\SentryClient\Service\ConfigurationService;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class ProductionExceptionHandler extends \TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler
{

    /**
     * Handles exceptions thrown during rendering of content objects
     * The handler can decide whether to re-throw the exception or
     * return a nice error message for production context.
     *
     * @param \Exception $exception
     * @param AbstractContentObject $contentObject
     * @param array $contentObjectConfiguration
     * @return string
     * @throws \Exception
     */
    public function handle(
        \Exception $exception,
        AbstractContentObject $contentObject = null,
        $contentObjectConfiguration = []
    ) {
        if ($exception instanceof PageNotFoundException && ConfigurationService::activatePageNotFoundHandling()) {
            $this->pageNotFoundAndExit($exception, $contentObject);
            // script dies here
        }

        $eventId = GeneralUtility::makeInstance(Client::class)->captureException($exception);
        $errorMessage = parent::handle($exception, $contentObject, $contentObjectConfiguration);

        if (ConfigurationService::showEventId()) {
            return sprintf('%s Event: %s', $errorMessage, $eventId);
        } else {
            return $errorMessage;
        }
    }

    /**
     * @param PageNotFoundException $exception
     * @param AbstractContentObject $contentObject
     */
    protected function pageNotFoundAndExit(PageNotFoundException $exception, AbstractContentObject $contentObject): void
    {
        if ($contentObject instanceof AbstractContentObject) {
            $currentRecord = $contentObject->getContentObjectRenderer()->currentRecord;
        }

        $reason = trim(sprintf(
            '%s: %s (code %s). %s',
            $exception->getTitle(),
            $exception->getMessage(),
            $exception->getCode(),
            $currentRecord ? 'Caused by record ' . $currentRecord : ''
        ));

        $GLOBALS['TSFE']->pageNotFoundAndExit($reason);
        // script dies here
    }
}