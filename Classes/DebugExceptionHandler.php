<?php
namespace Networkteam\SentryClient;

class DebugExceptionHandler extends \TYPO3\CMS\Core\Error\DebugExceptionHandler {

	/**
	 * @param \Exception|\Throwable $exception The throwable object.
	 * @throws \Exception
	 */
	public function handleException($exception) {
		$GLOBALS['USER']['sentryClient']->captureException($exception);
		parent::handleException($exception);
	}
}