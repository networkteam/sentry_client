<?php
namespace Networkteam\SentryClient;

class Client extends \Raven_Client {

	public function __construct() {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
			if (isset($configuration['dsn']) && $configuration['dsn'] != '') {
				parent::__construct($configuration['dsn']);
			}
		}
	}

	/**
	 * Log an exception to sentry
	 */
	public function captureException($exception, $culprit_or_options = NULL, $logger = NULL, $vars = NULL) {
		$production = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction();

		$this->tags_context(array(
			'typo3_version' => TYPO3_version,
			'typo3_mode' => TYPO3_MODE,
			'php_version' => phpversion(),
			'application_context' => $production === TRUE ? 'Production' : 'Development',
		));

		return parent::captureException($exception, $culprit_or_options, $logger, $vars);
	}
}