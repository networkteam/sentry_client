<?php

namespace Networkteam\SentryClient\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService implements \TYPO3\CMS\Core\SingletonInterface
{
    const DSN = 'dsn';

    const PRODUCTION_ONLY = 'productionOnly';

    const PAGE_NOT_FOUND_HANDLING_ACTIVE = 'activatePageNotFoundHandlingActive';

    const REPORT_USER_INFORMATION = 'reportUserInformation';

    const USER_INFORMATION_NONE = 'none';

    const USER_INFORMATION_USERID = 'userid';

    const USER_INFORMATION_USERNAMEEMAIL = 'usernameandemail';

    const REPORT_WITH_DEV_IP = 'reportWithDevIP';

    /**
     * @retun bool
     */
    public static function registerClient()
    {
        if (self::getDsn() === '') {
            return false;
        }

        if (self::isProductionOnly() && !GeneralUtility::getApplicationContext()->isProduction()) {
            return false;
        }

        $ipMatchesDevelopmentSystem = GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
        if (!self::reportWithDevIP() && $ipMatchesDevelopmentSystem) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed|null null is returned for $key not available in extension configuration
     */
    protected static function getExtensionConfiguration($key)
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);

        if (is_array($extensionConfiguration) && array_key_exists($key, $extensionConfiguration)) {
            return $extensionConfiguration[$key];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public static function getDsn()
    {
        return (string)self::getExtensionConfiguration(self::DSN);
    }

    /**
     * @return bool
     */
    public static function isProductionOnly()
    {
        $value = self::getExtensionConfiguration(self::PRODUCTION_ONLY);
        return $value === null ?: (bool)$value;
    }

    /**
     * @return bool
     */
    public static function isPageNotFoundHandlingActive()
    {
        $value = self::getExtensionConfiguration(self::PAGE_NOT_FOUND_HANDLING_ACTIVE);
        return $value === null ?: (bool)$value;
    }

    /**
     * @return string
     */
    public static function getReportUserInformation()
    {
        $value = self::getExtensionConfiguration(self::REPORT_USER_INFORMATION);
        switch ($value) {
            case self::USER_INFORMATION_NONE:
                return $value;
            case self::USER_INFORMATION_USERID:
                return $value;
            case self::USER_INFORMATION_USERNAMEEMAIL:
                return $value;
            default:
                return self::USER_INFORMATION_USERID;
        }
    }

    /**
     * @return bool
     */
    public static function reportWithDevIP()
    {
        $value = self::getExtensionConfiguration(self::REPORT_WITH_DEV_IP);
        return $value === null ? false : (bool)$value;
    }

}
