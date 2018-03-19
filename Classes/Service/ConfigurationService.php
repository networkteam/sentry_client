<?php
namespace Networkteam\SentryClient\Content;

use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

class ConfigurationService implements \TYPO3\CMS\Core\SingletonInterface {

	const DSN = 'dsn';

	const PRODUCTION_ONLY = 'productionOnly';

	const PAGE_NOT_FOUND_HANDLING_ACTIVE = 'PageNotFoundHandlingActive';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @param string $key Configuration key with dot notation (foo.bar.batz)
	 * @return mixed|null null is returned for $key not available in extension configuration
	 */
	public function getExtensionConfiguration($key) {
		/** @var ConfigurationUtility $configurationUtility */
		$configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
		$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
		$valuedExtensionConfiguration = $configurationUtility->convertNestedToValuedConfiguration($extensionConfiguration);

		if (array_key_exists($key, $valuedExtensionConfiguration)) {
			return $valuedExtensionConfiguration[$key]['value'];
		} else {
			return null;
		}
	}
}