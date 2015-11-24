<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

function register_client() {
	$ravenPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/raven/lib/Raven';
	require_once($ravenPath . '/Autoloader.php');
	\Raven_Autoloader::register();
	$client = new \Lemming\SentryClient\Client();
	$errorHandler = new Raven_ErrorHandler($client);
	$errorHandler->registerExceptionHandler();
	$errorHandler->registerErrorHandler();
	$errorHandler->registerShutdownFunction();
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
