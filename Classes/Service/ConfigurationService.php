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

    const IGNORE_MESSAGE_REGEX = 'ignoreMessageRegex';

    const REPORT_DATABASE_CONNECTION_ERRORS = 'reportDatabaseConnectionErrors';

    const SHOW_EVENT_ID = 'showEventId';

    const LOGWRITER_COMPONENT_IGNORELIST = 'logWriterComponentIgnorelist';

    const DISABLE_DATABASE_LOG = 'disableDatabaseLogging';

    protected static function getExtensionConfiguration(string $path): mixed
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client'][$path] ?? null;
    }

    public static function getExtConf(): ?array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client'] ?? null;
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
        return self::getExtensionConfiguration(self::REPORT_USER_INFORMATION) ?? '';
    }

    public static function getIgnoreMessageRegex(): ?string
    {
        return self::getExtensionConfiguration('messageBlacklistRegex') ?? self::getExtensionConfiguration(self::IGNORE_MESSAGE_REGEX);
    }

    public static function reportDatabaseConnectionErrors(): bool
    {
        return (bool)self::getExtensionConfiguration(self::REPORT_DATABASE_CONNECTION_ERRORS);
    }

    public static function showEventId(): bool
    {
        return (bool)self::getExtensionConfiguration(self::SHOW_EVENT_ID);
    }

    /**
     * @return string[]
     */
    public static function getLogWriterComponentIgnorelist(): array
    {
        $ignoreList = self::getExtensionConfiguration('logWriterComponentBlacklist') ?? self::getExtensionConfiguration(self::LOGWRITER_COMPONENT_IGNORELIST);
        return GeneralUtility::trimExplode(',', $ignoreList ?? '', true);
    }

    public static function shouldDisableDatabaseLogging(): bool
    {
        return (bool)self::getExtensionConfiguration(self::DISABLE_DATABASE_LOG);
    }
}
