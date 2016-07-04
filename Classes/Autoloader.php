<?php
namespace Lemming\SentryClient;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

use Raven_ErrorHandler;

class Autoloader {

	public static function registerClient() {
		$ravenPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'Resources/Private/Libraries/sentry/sentry/lib/Raven';
		require_once($ravenPath . '/Autoloader.php');
		\Raven_Autoloader::register();
		$client = new \Lemming\SentryClient\Client();
		$errorHandler = new Raven_ErrorHandler($client, TRUE);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerShutdownFunction();
	}
}