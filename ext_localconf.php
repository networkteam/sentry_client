<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (!function_exists('register_client')) {
	function register_client() {
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client');
		$autoloadPath = $extPath . 'vendor/autoload.php';
		if (is_readable($autoloadPath)) {
			require_once $autoloadPath;
		}
		require_once $extPath . 'Classes/Client.php';

		$client = new \Lemming\SentryClient\Client();
		$errorHandler = new Raven_ErrorHandler($client, TRUE);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerShutdownFunction();
	}
}

if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
	$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
	if (isset($configuration['dsn']) && $configuration['dsn'] != '') {

		if (isset($configuration['productionOnly']) && (bool)$configuration['productionOnly'] === TRUE) {
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()) {
				register_client();
			}
		} else {
			register_client();
		}
	}
}
