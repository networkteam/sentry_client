<?php
declare(strict_types = 1);
namespace Networkteam\SentryClient\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService
{
    const DSN = 'dsn';

    const REPORT_USER_INFORMATION = 'reportUserInformation';

    const USER_INFORMATION_NONE = 'none';

    const MESSAGE_BLACKLIST_REGEX = 'messageBlacklistRegex';

    const REPORT_DATABASE_CONNECTION_ERRORS = 'reportDatabaseConnectionErrors';

    const SHOW_EVENT_ID = 'showEventId';

    const LOGWRITER_LOGLEVEL = 'logWriterLogLevel';

    const LOGWRITER_COMPONENT_BLACKLIST = 'logWriterComponentBlacklist';

    const DISABLE_DATABASE_LOG = 'disableDatabaseLogging';

    protected static function getExtensionConfiguration(string $path): mixed
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client'][$path] ?? null;
    }

    public static function getExtConf(): ?array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client']['options'] ?? null;
    }

    public static function getDsn(): ?string
    {
        $dsn = getenv('SENTRY_DSN') ?: self::getExtensionConfiguration(self::DSN);

        return !empty($dsn) ? $dsn : null;
    }

    public static function getEnvironment(): string
    {
        return getenv('SENTRY_ENVIRONMENT') ?: self::getNormalizedApplicationContext();
    }

    public static function getRelease(): ?string
    {
        return getenv('SENTRY_RELEASE') ?: self::getExtensionConfiguration('release');
    }

    protected static function getNormalizedApplicationContext(): string
    {
        return preg_replace("/[^a-zA-Z0-9]/", "-", Environment::getContext()->__toString());
    }

    public static function getReportUserInformation(): string
    {
        return self::getExtensionConfiguration(self::REPORT_USER_INFORMATION);
    }

    public static function getMessageBlacklistRegex(): ?string
    {
        return self::getExtensionConfiguration(self::MESSAGE_BLACKLIST_REGEX);
    }

    public static function reportDatabaseConnectionErrors(): bool
    {
        return (bool)self::getExtensionConfiguration(self::REPORT_DATABASE_CONNECTION_ERRORS);
    }

    public static function showEventId(): bool
    {
        return (bool)self::getExtensionConfiguration(self::SHOW_EVENT_ID);
    }

    public static function getLogWriterLevel(): string
    {
        return (string)self::getExtensionConfiguration(self::LOGWRITER_LOGLEVEL);
    }

    /**
     * @return string[]
     */
    public static function getLogWriterComponentBlacklist(): array
    {
        return GeneralUtility::trimExplode(',', self::getExtensionConfiguration(self::LOGWRITER_COMPONENT_BLACKLIST), true);
    }

    public static function shouldDisableDatabaseLogging(): bool
    {
        return (bool)self::getExtensionConfiguration(self::DISABLE_DATABASE_LOG);
    }
}
