<?php
declare(strict_types = 1);
namespace Networkteam\SentryClient\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService
{
    const DISABLE_SENTRY = 'disableSentry';

    const DSN = 'dsn';

    const REPORT_USER_INFORMATION = 'reportUserInformation';

    const USER_INFORMATION_NONE = 'none';

    const USER_INFORMATION_USERNAMEEMAIL = 'usernameandemail';

    const MESSAGE_BLACKLIST_REGEX = 'messageBlacklistRegex';

    const REPORT_DATABASE_CONNECTION_ERRORS = 'reportDatabaseConnectionErrors';

    const SHOW_EVENT_ID = 'showEventId';

    const LOGWRITER_LOGLEVEL = 'logWriterLogLevel';

    const LOGWRITER_COMPONENT_BLACKLIST = 'logWriterComponentBlacklist';

    /**
     * @param string $path
     * @return mixed
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    protected static function getExtensionConfiguration(string $path)
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        return $extensionConfiguration->get('sentry_client', $path);
    }

    public static function isDisabled(): bool
    {
        return (bool)getenv('SENTRY_DISABLE') || (bool)self::getExtensionConfiguration(self::DISABLE_SENTRY);
    }

    public static function getDsn(): ?string
    {
        return getenv('SENTRY_DSN') ?: self::getExtensionConfiguration(self::DSN);
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
        return preg_replace("/[^a-zA-Z0-9]/", "-", (string)Environment::getContext());
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

    public static function getLogWriterComponentBlacklist(): string
    {
        return (string)self::getExtensionConfiguration(self::LOGWRITER_COMPONENT_BLACKLIST);
    }
}
