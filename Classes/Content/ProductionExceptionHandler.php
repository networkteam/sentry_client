<?php
namespace Networkteam\SentryClient\Content;

use Networkteam\SentryClient\Service\ConfigurationService;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class ProductionExceptionHandler extends \TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler {

	/**
	 * Handles exceptions thrown during rendering of content objects
	 * The handler can decide whether to re-throw the exception or
	 * return a nice error message for production context.
	 *
	 * @param \Exception $exception
	 * @param AbstractContentObject $contentObject
	 * @param array $contentObjectConfiguration
	 * @return string
	 * @throws \Exception
	 */
	public function handle(\Exception $exception, AbstractContentObject $contentObject = null, $contentObjectConfiguration = []) {
		if (ConfigurationService::isPageNotFoundHandlingActive() && $exception instanceof PageNotFoundException) {
			$this->pageNotFoundAndExit($exception, $contentObject);
			// script dies here
		}

		$GLOBALS['USER']['sentryClient']->captureException($exception);
		return parent::handle($exception, $contentObject, $contentObjectConfiguration);
	}

	/**
	 * @param PageNotFoundException $exception
	 * @param AbstractContentObject $contentObject
	 */
	protected function pageNotFoundAndExit(PageNotFoundException $exception, AbstractContentObject $contentObject) {
		if ($contentObject instanceof AbstractContentObject) {
			$currentRecord = $contentObject->getContentObjectRenderer()->currentRecord;
		}

		$header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
		$reason = trim(sprintf(
			'%s: %s (code %s). %s',
			$exception->getTitle(),
			$exception->getMessage(),
			$exception->getCode(),
			$currentRecord ? 'Caused by record ' . $currentRecord : ''
		));
		$GLOBALS['TSFE']->pageNotFoundAndExit($reason, $header);
		// script dies here
	}
}