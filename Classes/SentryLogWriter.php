<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ExceptionBlacklistService;
use Sentry\Event;
use Sentry\Stacktrace;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

use function Sentry\withScope;

class SentryLogWriter extends AbstractWriter
{

    /**
     * Forwards the log record to Sentry
     *
     * @param LogRecord $record Log record
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     */
    public function writeLog(LogRecord $record)
    {
        if (ExceptionBlacklistService::shouldHandleLogMessage($record) && Client::init()) {
            withScope(function (Scope $scope) use ($record): void {
                $scope->setExtra('component', $record->getComponent());
                if ($record->getData()) {
                    $scope->setExtra('data', $record->getData());
                }
                $scope->setTag('source', 'logwriter');

                Client::captureMessage($record->getMessage(), $record->getLevel());
            });
        }

        return $this;
    }

    public static function cleanupStacktrace(Event $event): Event
    {
        if (($event->getTags()['source'] ?? false) === 'logwriter') {
            $stacktrace = $event->getStacktrace();
            foreach($stacktrace->getFrames() as $no => $frame) {
                if (str_starts_with($frame->getFunctionName() ?? '', 'Psr\Log\AbstractLogger::')) {
                    $stacktraceBeforeLogCall = new Stacktrace(array_slice($stacktrace->getFrames(), 0, $no));
                    $event->setStacktrace($stacktraceBeforeLogCall);
                    break;
                }
            }
        }

        return $event;
    }
}
