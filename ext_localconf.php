<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (!function_exists('register_client')) {
	function register_client() {
		$ravenPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/raven/lib/Raven';
		require_once($ravenPath . '/Autoloader.php');
		\Raven_Autoloader::register();
		$GLOBALS['USER']['sentryClient'] = new \Lemming\SentryClient\Client();
		$errorHandler = new Raven_ErrorHandler($GLOBALS['USER']['sentryClient'], TRUE);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerShutdownFunction();

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\Exception\\ProductionExceptionHandler'] = array(
			'className' => 'Networkteam\\SentryClient\\Content\\ProductionExceptionHandler'
		);
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = 'Networkteam\\SentryClient\\DebugExceptionHandler';
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = 'Networkteam\\SentryClient\\ProductionExceptionHandler';
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
