<?php
namespace Networkteam\SentryClient\Service;

class ConfigurationService implements \TYPO3\CMS\Core\SingletonInterface {

	const DSN = 'dsn';

	const PRODUCTION_ONLY = 'productionOnly';

	const PAGE_NOT_FOUND_HANDLING_ACTIVE = 'pageNotFoundHandlingActive';

	const IGNORE_BACKEND_REQUEST = 'ignoreBackendRequests';

	const IGNORE_FRONTEND_REQUEST = 'ignoreFrontendRequests';

	const REPORT_BACKENDUSER_INFORMATION = 'reportBackenduserInformation';

	const REPORT_FRONTENDUSER_INFORMATION = 'reportFrontenduserInformation';

	/**
	 * @return mixed|null null is returned for $key not available in extension configuration
	 */
	protected static function getExtensionConfiguration($key) {
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
	public static function getDsn() {
		return (string)self::getExtensionConfiguration(self::DSN);
	}

	/**
	 * @return bool
	 */
	public static function isProductionOnly() {
		$value = self::getExtensionConfiguration(self::PRODUCTION_ONLY);
		return $value === null ?: $value;
	}

	/**
	 * @return bool
	 */
	public static function isPageNotFoundHandlingActive() {
		$value = self::getExtensionConfiguration(self::PAGE_NOT_FOUND_HANDLING_ACTIVE);
		return $value === null ?: $value;
	}

	/**
	 * @return bool
	 */
	public static function isReportBackenduserInformation() {
		$value = self::getExtensionConfiguration(self::REPORT_BACKENDUSER_INFORMATION);
		return $value === null ?: $value;
    }

	/**
	 * @return bool
	 */
	public static function isReportFrontenduserInformation() {
		$value = self::getExtensionConfiguration(self::REPORT_FRONTENDUSER_INFORMATION);
		return $value === null ?: $value;
    }

}