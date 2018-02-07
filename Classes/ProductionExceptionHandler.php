<?php
namespace Networkteam\SentryClient;

class ProductionExceptionHandler extends \TYPO3\CMS\Core\Error\ProductionExceptionHandler {

	/**
	 * @param \Exception|\Throwable $exception The throwable object.
	 * @throws \Exception
	 */
	public function handleException($exception) {
		$GLOBALS['USER']['sentryClient']->captureException($exception);
		parent::handleException($exception);
	}
}