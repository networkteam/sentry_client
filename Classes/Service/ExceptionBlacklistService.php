<?php
declare(strict_types=1);
namespace Networkteam\SentryClient\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class ExceptionBlacklistService
{
    public static function shouldHandleException(\Throwable $exception): bool
    {
        if (self::messageMatchesBlacklistRegex($exception->getMessage())) {
            return false;
        }

        if (
            $exception instanceof ImmediateResponseException ||
            $exception instanceof PageNotFoundException
        ) {
            return false;
        }

        if (!ConfigurationService::reportDatabaseConnectionErrors()) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
            if (!$connection->isConnected()) {
                return false;
            }
        }

        return true;
    }

    public static function shouldHandleLogMessage(LogRecord $logRecord): bool
    {
        if ($logRecord->getComponent() === 'TYPO3.CMS.Frontend.ContentObject.Exception.ProductionExceptionHandler') {
            return false;
        }

        if (self::messageMatchesBlacklistRegex($logRecord->getMessage())) {
            return false;
        }

        $componentBlacklist = GeneralUtility::trimExplode(',', ConfigurationService::getLogWriterComponentBlacklist(), true);
        foreach ($componentBlacklist as $componentInBlacklist) {
            if (str_starts_with($logRecord->getComponent() . '.', $componentInBlacklist . '.')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $message
     * @return bool
     */
    protected static function messageMatchesBlacklistRegex($message): bool
    {
        $regex = ConfigurationService::getMessageBlacklistRegex();
        if (!empty($regex) && !empty($message)) {
            return preg_match($regex, $message) === 1;
        }

        return false;
    }
}
