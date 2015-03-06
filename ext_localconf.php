<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
	$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
	if (isset($configuration['dsn']) && $configuration['dsn'] != '') {
		$ravenPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/raven/lib/Raven';
		require_once($ravenPath . '/Autoloader.php');
		\Raven_Autoloader::register();

		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['errors']['exceptionHandler'] === $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['errors']['exceptionHandler'] = 'Lemming\\SentryClient\\Handler\\ProductionExceptionHandler';
		} else {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['errors']['exceptionHandler'] = 'Lemming\\SentryClient\\Handler\\DebugExceptionHandler';
		}
	}
}