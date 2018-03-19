<?php
namespace Networkteam\SentryClient\Content;

use Networkteam\SentryClient\Content\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class ProductionExceptionHandler extends \TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler {

	/**
	 * @var ConfigurationService
	 */
	protected $configurationService;

	public function __construct(array $configuration = [])
	{
		parent::__construct($configuration);

		$objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
		$this->configurationService = $objectManager->get(ConfigurationService::class);
	}

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
		$isPageNotFoundHandlingActive = $this->configurationService->getExtensionConfiguration(ConfigurationService::PAGE_NOT_FOUND_HANDLING_ACTIVE);

		if ($isPageNotFoundHandlingActive && $exception instanceof \TYPO3\CMS\Core\Error\Http\PageNotFoundException) {
			if ($contentObject instanceof AbstractContentObject) {
		 		$currentRecord = $contentObject->getContentObjectRenderer()->currentRecord;
			}

			$header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
			$reason = trim(sprintf('%s: %s (code %s). %s', $exception->getTitle(), $exception->getMessage(), $exception->getCode(), $currentRecord ? 'Caused by record ' . $currentRecord : ''));
			// script dies here
			$GLOBALS['TSFE']->pageNotFoundAndExit($reason, $header);
		}

		$GLOBALS['USER']['sentryClient']->captureException($exception);
		return parent::handle($exception, $contentObject, $contentObjectConfiguration);
	}
}