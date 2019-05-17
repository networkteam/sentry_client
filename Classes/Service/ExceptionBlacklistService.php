<?php

namespace Networkteam\SentryClient\Service;

class ExceptionBlacklistService
{
    public static function shouldHandleException(\Throwable $exception)
    {
        if (self::messageMatchesBlacklistRegex($exception->getMessage())) {
            return false;
        }

        if (!ConfigurationService::reportDatabaseConnectionErrors() && self::isDatabaseConnectionException($exception)) {
            return false;
        }

        return true;
    }

    protected static function isDatabaseConnectionException($exception)
    {
        $messageRegex = [
            'Cannot connect to the configured database',
            'An exception occured in driver: No such file or directory',
            'An exception occured in driver: Access denied for user',
            'MySQL server has gone away',
            'Solr returned an error: 503 Service Unavailable',
        ];

        foreach ($messageRegex as $pattern) {
            if (preg_match('/' . $pattern . '/', $exception->getMessage())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $message
     * @return bool
     */
    protected static function messageMatchesBlacklistRegex($message)
    {
        $regex = ConfigurationService::getMessageBlacklistRegex();
        if (!empty($regex) && !empty($message)) {
            return preg_match($regex, $message) === 1;
        }

        return false;
    }
}