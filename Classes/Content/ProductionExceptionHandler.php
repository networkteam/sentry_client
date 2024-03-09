<?php

namespace Networkteam\SentryClient\Content;

use Networkteam\SentryClient\Client;
use Networkteam\SentryClient\Service\ConfigurationService;
use Sentry\State\Scope;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use function Sentry\withScope;

class ProductionExceptionHandler extends \TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler
{

    /**
     * Handles exceptions thrown during rendering of content objects
     * The handler can decide whether to re-throw the exception or
     * return a nice error message for production context.
     */
    public function handle(
        \Exception $exception,
        AbstractContentObject $contentObject = null,
        $contentObjectConfiguration = []
    ): string {
        if (Environment::getContext()->isDevelopment()) {
            throw $exception;
        }

        $eventId = '';
        withScope(function (Scope $scope) use ($exception, $contentObject, &$eventId): void {
            $currentRecord = $contentObject?->getContentObjectRenderer()?->currentRecord;
            if (!empty($currentRecord)) {
                $scope->setExtra('Edit record', $this->getEditUri($currentRecord));
            }
            $eventId = Client::captureException($exception);
        });

        $errorMessage = parent::handle($exception, $contentObject, $contentObjectConfiguration);

        if (ConfigurationService::showEventId()) {
            return sprintf('%s Event: %s', $errorMessage, $eventId);
        }
        return $errorMessage;
    }

    protected function getEditUri(string $currentRecord): ?string
    {
        [$tableName, $uid] = GeneralUtility::trimExplode(':', $currentRecord);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        try {
            $editUri = (string)$uriBuilder->buildUriFromRoute(
                'record_edit',
                [
                    'edit[' . $tableName . '][' . $uid . ']' => 'edit',
                ],
                UriBuilder::SHAREABLE_URL
            );
            return $editUri;
        } catch (\Throwable) {}
        return null;
    }
}
