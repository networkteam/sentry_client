<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (!function_exists('register_client')) {
	function register_client() {

		$autoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/autoload.php';
		if (file_exists($autoloadFile)) {
			require_once($autoloadFile);
		}

		\Raven_Autoloader::register();
		$GLOBALS['USER']['sentryClient'] = new \Networkteam\SentryClient\Client();
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

if (\Networkteam\SentryClient\Service\ConfigurationService::getDsn() !== '') {
    if (\Networkteam\SentryClient\Service\ConfigurationService::isProductionOnly()) {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()) {
            register_client();
        }
    } else {
        register_client();
    }
}
