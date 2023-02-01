<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ExceptionBlacklistService;
use Networkteam\SentryClient\Service\SentryService;
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
        if (SentryService::isEnabled()
            && ExceptionBlacklistService::shouldHandleLogMessage($record)
        ) {
            withScope(function (Scope $scope) use ($record): void {
                $scope->setExtra('component', $record->getComponent());
                if ($record->getData()) {
                    $scope->setExtra('data', $record->getData());
                }
                $scope->setTag('source', 'logwriter');
                $scope->setFingerprint([
                    $record->getMessage(),
                    $record->getComponent()
                ]);
                
                $message = $record->getMessage();
                if (method_exists($this, 'interpolate')) {
                    $message = $this->interpolate($message, $record->getData());
                }

                Client::captureMessage($message, $record->getLevel());
            });
        }

        return $this;
    }

    public static function cleanupStacktrace(Event $event): Event
    {
        if (($event->getTags()['source'] ?? false) === 'logwriter') {
            $stacktrace = $event->getStacktrace();
            if ($stacktrace instanceof Stacktrace) {
                foreach($stacktrace->getFrames() as $no => $frame) {
                    if (str_starts_with($frame->getFunctionName() ?? '', 'Psr\Log\AbstractLogger::')) {
                        $stacktraceBeforeLogCall = new Stacktrace(array_slice($stacktrace->getFrames(), 0, $no));
                        $event->setStacktrace($stacktraceBeforeLogCall);
                        break;
                    }
                }
            }
        }

        return $event;
    }
}
