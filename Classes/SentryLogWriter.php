<?php

namespace Networkteam\SentryClient;

use Sentry\State\Scope;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use function Sentry\withScope;

class SentryLogWriter extends AbstractWriter
{
    public function writeLog(LogRecord $record)
    {
        if ($record->getComponent() !== 'TYPO3.CMS.Frontend.ContentObject.Exception.ProductionExceptionHandler' &&
            Client::init()
        ) {
            withScope(function (Scope $scope) use ($record): void {
                $scope->setExtra('component', $record->getComponent());
                if ($record->getData()) {
                    $scope->setExtra('data', $record->getData());
                }
                $scope->setTag('source', 'logwriter');

                Client::captureMessage($record->getMessage(), $record->getLevel());
            });
        }
    }
}
