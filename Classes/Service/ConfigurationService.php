<?php
namespace Networkteam\SentryClient\Service;

class ConfigurationService implements \TYPO3\CMS\Core\SingletonInterface {

	const DSN = 'dsn';

	const PRODUCTION_ONLY = 'productionOnly';

	const PAGE_NOT_FOUND_HANDLING_ACTIVE = 'pageNotFoundHandlingActive';

	/**
	 * @param string $key Configuration key with dot notation (foo.bar.batz)
	 * @return mixed|null null is returned for $key not available in extension configuration
	 */
	protected static function getExtensionConfiguration($key) {
		$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);

		if (array_key_exists($key, $extensionConfiguration)) {
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
		return (bool)self::getExtensionConfiguration(self::PRODUCTION_ONLY);
	}

	/**
	 * @return bool
	 */
	public static function isPageNotFoundHandlingActive() {
		return (bool)self::getExtensionConfiguration(self::PAGE_NOT_FOUND_HANDLING_ACTIVE);
	}

}