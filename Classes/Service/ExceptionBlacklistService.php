<?php
declare(strict_types=1);
namespace Networkteam\SentryClient\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExceptionBlacklistService
{
    public static function shouldHandleException(\Throwable $exception): bool
    {
        if (self::messageMatchesBlacklistRegex($exception->getMessage())) {
            return false;
        }

        if ($exception instanceof \TYPO3\CMS\Core\Http\ImmediateResponseException) {
            return false;
        }

        if (!ConfigurationService::reportDatabaseConnectionErrors()) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            if (!$queryBuilder->getConnection()->isConnected()) {
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