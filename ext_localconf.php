<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
	$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);

	if (isset($configuration['dsn']) && $configuration['dsn'] != '') {
		if (isset($configuration['productionOnly']) && (bool)$configuration['productionOnly'] === TRUE) {
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()) {
				\Lemming\SentryClient\Autoloader::registerClient();
			}
		} else {
			\Lemming\SentryClient\Autoloader::registerClient();
		}
	}
}
